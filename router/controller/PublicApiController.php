<?php

namespace uve\router;

use Throwable;
use uve\core\Core;
use uve\core\Logger;
use uve\core\module\action\service\ActionService;
use uve\core\module\user\service\SessionService;
use uve\core\module\user\service\UserMailService;
use uve\core\module\user\value\UserJourneyStatus;
use uve\core\module\user\value\UserRole;
use uve\core\util\StringUtil;
use uve\core\util\UrlBuilderUtil;
use uve\core\value\Language;
use uve\router\controller\response\PublicApiControllerForgotPasswordFailureResponse;
use uve\router\controller\response\PublicApiControllerForgotPasswordSuccessResponse;
use uve\router\controller\response\PublicApiControllerGetSessionSuccessResponse;
use uve\router\controller\response\PublicApiControllerLogErrorSuccessResponse;
use uve\router\controller\response\PublicApiControllerPingSuccessResponse;
use uve\router\controller\response\PublicApiControllerRequestRegistrationInviteFailureResponse;
use uve\router\controller\response\PublicApiControllerRequestRegistrationInviteSuccessResponse;
use uve\router\controller\response\PublicApiControllerUserLoginSuccessResponse;
use uve\core\model\Request;
use uve\core\model\Response;
use uve\core\module\user\service\UserService;
use uve\router\controller\response\PublicApiControllerUserPasswordResetFailureResponse;
use uve\router\controller\response\PublicApiControllerUserPasswordResetSuccessResponse;
use uve\router\controller\response\PublicApiControllerUserRegistrationSuccessResponse;

final class PublicApiController extends PublicApiControllerAbstract
{
    public function __construct(
        private Logger $logger,
        private UserService $userService,
        private SessionService $sessionService,
        private UserMailService $mailService,
        private ActionService $actionService
    ) {
        parent::__construct();
    }

    public function authenticate(Request $request): bool
    {
        $this->actionService->logAction($request, $request->getSession());

        return true;
    }

    /**
     * Endpoint: /papi/ping
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function ping(Request $request): Response
    {
        return new PublicApiControllerPingSuccessResponse();
    }

    /**
     * Endpoint: /papi/log
     * Method: POST
     *
     * @param string|null $endpoint
     * @param string|null $method
     * @param string|null $payload
     * @param string|null $errorMessage
     * @param string|null $userAgent
     * @param string|null $pageUrl
     * @param Request $request
     * @return Response
     */
    protected function logError(
        ?string $endpoint,
        ?string $method,
        ?string $payload,
        ?string $errorMessage,
        ?string $userAgent,
        ?string $pageUrl,
        Request $request
    ): Response {
        try {
            $this->logger->logError(
                'AJAX Logger' .
                ' - Error message: ' . $errorMessage .
                ' - Endpoint: ' . $method . ' ' . $endpoint .
                ' - Payload: ' . $payload .
                ' - Page URL: ' . $pageUrl .
                ' - User agent: ' . $userAgent
            );
        } catch (Throwable $t) {
            // Ignore and move on
        }

        return new PublicApiControllerLogErrorSuccessResponse();
    }

    /**
     * Endpoint: /papi/session
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getSession(Request $request): Response
    {
        $user = $request->getSession()
            ? $request->getSession()->getUser()
            : null;

        $userArray = [];
        if ($user) {
            $userArray = $user->asArray();
            $userArray['language_name'] = Language::getNameForId($user->getLanguageId());
            $userArray['role_name'] = UserRole::getNameForId($user->getRoleId());
            $userArray['journey_status_name'] = UserJourneyStatus::getNameForId($user->getJourneyStatusId());
            unset($userArray['password_hash']);
        }

        $output['user'] = $userArray;
        return new PublicApiControllerGetSessionSuccessResponse($output);
    }

    /**
     * Endpoint: /api/login
     * Method: POST
     *
     * @param string $user
     * @param string $password
     * @param string $languageIsoCode
     * @param Request $request
     * @return Response
     */
    protected function userLogin(
        string $user,
        string $password,
        string $languageIsoCode,
        Request $request
    ): Response {
        $languageIsoCode = in_array(strtoupper($languageIsoCode), Language::getAvailableIsoCodes())
            ? strtolower($languageIsoCode)
            : Core::getConfigValue('defaultSiteLanguage');
        $user = $this->userService->verifyUser($user, $password);
        $localisationUtil = Core::getLocalisationUtil($languageIsoCode);

        if (empty($user)) {
            return new PublicApiControllerUserLoginSuccessResponse(
                false,
                null,
                $localisationUtil->getValue('authenticationEmailAndOrPassNotValid')
            );
        }

        $session = $this->sessionService->login(
            $user,
            $user->getTimezone(),
            $request->getSourceIp(),
            $request->getUserAgent()
        );

        if (empty($session)) {
            return new PublicApiControllerUserLoginSuccessResponse(
                false,
                null,
                $localisationUtil->getValue('authenticationEmailAndOrPassNotValid')
            );
        }

        $baseLinkUrl = UrlBuilderUtil::getBaseLinkUrl($languageIsoCode);
        return new PublicApiControllerUserLoginSuccessResponse(
            true,
            $session->isAdmin()
                ? $baseLinkUrl . UrlBuilderUtil::BACKOFFICE_DASHBOARD_URL_PATH
                : $baseLinkUrl . UrlBuilderUtil::APP_DASHBOARD_URL_PATH
        );
    }

    /**
     * Endpoint: /papi/login/forgot
     * Method: POST
     *
     * @param string $email
     * @param Request $request
     * @return Response
     */
    protected function forgotPassword(string $email, Request $request): Response
    {
        $existingUser =$this->userService->getUserForEmail($email);
        if (empty($existingUser)) {
            return new PublicApiControllerForgotPasswordFailureResponse();
        }

        $res = $this->mailService->sendPasswordResetEmail($existingUser);
        return new PublicApiControllerForgotPasswordSuccessResponse($res);
    }

    /**
     * Endpoint: /papi/register
     * Method: POST
     *
     * @param string $languageIsoCode
     * @param string $email
     * @param string $password
     * @param string $name
     * @param int $timezoneOffsetMinutes
     * @param Request $request
     * @return Response
     */
    protected function userRegistration(
        string $languageIsoCode,
        string $email,
        string $password,
        string $name,
        int $timezoneOffsetMinutes,
        Request $request
    ): Response {
        $languageIsoCode = in_array(strtoupper($languageIsoCode), Language::getAvailableIsoCodes())
            ? strtolower($languageIsoCode)
            : Core::getConfigValue('defaultSiteLanguage');
        $localisationUtil = Core::getLocalisationUtil($languageIsoCode);

        try {
            $isRegistrationEnabled = Core::getConfigValue('registrationEnabled');
            if (!$isRegistrationEnabled) {
                return new PublicApiControllerUserRegistrationSuccessResponse(
                    false,
                    null,
                    $localisationUtil->getValue('authenticationUserRegistrationDisabled')
                );
            }

            $email = StringUtil::normaliseEmail($email);
            if (!StringUtil::isEmailAddressValid($email)) {
                return new PublicApiControllerUserRegistrationSuccessResponse(
                    false,
                    null,
                    $localisationUtil->getValue('authenticationEmailNotValid')
                );
            }

            if (strlen($password) < UserService::USER_PASSWORD_MIN_LENGTH) {
                return new PublicApiControllerUserRegistrationSuccessResponse(
                    false,
                    null,
                    $localisationUtil->getValue('authenticationPasswordTooShort')
                );
            }

            $existingUser =$this->userService->getUserForEmail($email);
            if (!empty($existingUser)) {
                return new PublicApiControllerUserRegistrationSuccessResponse(
                    false,
                    null,
                    sprintf(
                        $localisationUtil->getValue('authenticationRegistrationErrorExistingEmail'),
                        UrlBuilderUtil::getBaseLinkUrl($languageIsoCode) . '/login'
                    )
                );
            }

            $languageId = Language::getIdForIsoCode($languageIsoCode);
            // ToDo
            $user = $this->userService->storeUser(
                new User(

                )

            );
            $res = empty($user) ? false : true;

            $baseLinkUrl = UrlBuilderUtil::getBaseLinkUrl($languageIsoCode);
            return new PublicApiControllerUserRegistrationSuccessResponse(
                $res,
                $res
                    ? $baseLinkUrl . UrlBuilderUtil::APP_DASHBOARD_URL_PATH
                    : $baseLinkUrl
            );
        } catch (Throwable $t) {
            $this->logger->logError('Error registering user: ' . $t->getMessage());
            return new PublicApiControllerUserRegistrationSuccessResponse(false);
        }
    }

    /**
     * Endpoint: /papi/password-reset
     * Method: POST
     *
     * @param int $userId
     * @param string $password
     * @param string $passwordConfirmation
     * @param string $verificationHash
     * @param string $languageIsoCode
     * @param Request $request
     * @return Response
     */
    protected function userPasswordReset(
        int $userId,
        string $password,
        string $passwordConfirmation,
        string $verificationHash,
        string $languageIsoCode,
        Request $request
    ): Response {
        $languageIsoCode = in_array(strtoupper($languageIsoCode), Language::getAvailableIsoCodes())
            ? strtolower($languageIsoCode)
            : Core::getConfigValue('defaultSiteLanguage');
        $localisationUtil = Core::getLocalisationUtil($languageIsoCode);
        $user = $this->userService->getUserForId($userId);
        if (empty($user) || !$user->validateValidationHash($verificationHash)) {
            return new PublicApiControllerUserPasswordResetFailureResponse();
        }

        if (strlen($password) < UserService::USER_PASSWORD_MIN_LENGTH) {
            return new PublicApiControllerUserPasswordResetSuccessResponse(
                false,
                $localisationUtil->getValue('authenticationPasswordTooShort')
            );
        }

        if ($passwordConfirmation != $password) {
            return new PublicApiControllerUserPasswordResetSuccessResponse(
                false,
                $localisationUtil->getValue('authenticationPasswordsDoNotMatch')
            );
        }

        $res = $this->userService->updatePassword($userId, $password);
        return new PublicApiControllerUserPasswordResetSuccessResponse($res);
    }

    /**
     * Endpoint: /papi/invite-register
     * Method: POST
     *
     * @param string $email
     * @param string|null $languageIsoCode
     * @param Request $request
     * @return Response
     */
    protected function requestRegistrationInvite(
        string $email,
        ?string $languageIsoCode,
        Request $request
    ): Response {
        $isInvitationEnabled = Core::getConfigValue('invitationEnabled');
        if (!$isInvitationEnabled) {
            return new PublicApiControllerRequestRegistrationInviteFailureResponse();
        }

        $res = $this->userService->storeRegistrationInviteRequest(
            $email,
            Language::getIdForIsoCode($languageIsoCode)
        );

        return new PublicApiControllerRequestRegistrationInviteSuccessResponse(
            $res ? true : false
        );
    }
}
