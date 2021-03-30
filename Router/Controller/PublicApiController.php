<?php

namespace Amora\Router;

use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Value\VerificationType;
use Amora\Core\Util\DateUtil;
use Amora\Router\Controller\Response\PublicApiControllerUserPasswordCreationFailureResponse;
use Amora\Router\Controller\Response\PublicApiControllerUserPasswordCreationSuccessResponse;
use Throwable;
use Amora\Core\Core;
use Amora\Core\Logger;
use Amora\Core\Module\Action\Service\ActionService;
use Amora\Core\Module\User\Service\SessionService;
use Amora\Core\Module\User\Service\UserMailService;
use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\Language;
use Amora\Router\Controller\Response\PublicApiControllerForgotPasswordSuccessResponse;
use Amora\Router\Controller\Response\PublicApiControllerGetSessionSuccessResponse;
use Amora\Router\Controller\Response\PublicApiControllerLogErrorSuccessResponse;
use Amora\Router\Controller\Response\PublicApiControllerPingSuccessResponse;
use Amora\Router\Controller\Response\PublicApiControllerRequestRegistrationInviteFailureResponse;
use Amora\Router\Controller\Response\PublicApiControllerRequestRegistrationInviteSuccessResponse;
use Amora\Router\Controller\Response\PublicApiControllerUserLoginSuccessResponse;
use Amora\Core\Model\Request;
use Amora\Core\Model\Response;
use Amora\Core\Module\User\Service\UserService;
use Amora\Router\Controller\Response\PublicApiControllerUserPasswordResetFailureResponse;
use Amora\Router\Controller\Response\PublicApiControllerUserPasswordResetSuccessResponse;
use Amora\Router\Controller\Response\PublicApiControllerUserRegistrationSuccessResponse;

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
        if (empty($existingUser) || !$existingUser->isEnabled()) {
            return new PublicApiControllerForgotPasswordSuccessResponse(true);
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
            $roleId = UserRole::USER;
            $now = DateUtil::getCurrentDateForMySql();
            $user = $this->userService->storeUser(
                user: new User(
                    id: null,
                    languageId: $languageId,
                    roleId: $roleId,
                    journeyStatusId: UserJourneyStatus::REGISTRATION,
                    createdAt: $now,
                    updatedAt: $now,
                    email: $email,
                    name: $name,
                    passwordHash: StringUtil::hashPassword($password),
                    bio: null,
                    isEnabled: true,
                    verified: false,
                    timezone: DateUtil::getTimezoneFromUtcOffset($timezoneOffsetMinutes)
                ),
                verificationEmailId: VerificationType::EMAIL_ADDRESS
            );
            $res = empty($user) ? false : true;

            $session = $this->sessionService->login(
                $user,
                $user->getTimezone(),
                $request->getSourceIp(),
                $request->getUserAgent()
            );

            $baseLinkUrl = UrlBuilderUtil::getBaseLinkUrl($languageIsoCode);
            return new PublicApiControllerUserRegistrationSuccessResponse(
                $res,
                $session->isAdmin()
                    ? $baseLinkUrl . UrlBuilderUtil::BACKOFFICE_DASHBOARD_URL_PATH
                    : $baseLinkUrl . UrlBuilderUtil::APP_DASHBOARD_URL_PATH
            );
        } catch (Throwable $t) {
            $this->logger->logError('Error registering user: ' . $t->getMessage());
            return new PublicApiControllerUserRegistrationSuccessResponse(false);
        }
    }

    /**
     * Endpoint: /papi/login/password-reset
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
     * Endpoint: /papi/login/password-creation
     * Method: POST
     *
     * @param int $userId
     * @param string $password
     * @param string $passwordConfirmation
     * @param string $verificationHash
     * @param string $verificationIdentifier
     * @param string $languageIsoCode
     * @param Request $request
     * @return Response
     */
    protected function userPasswordCreation(
        int $userId,
        string $password,
        string $passwordConfirmation,
        string $verificationHash,
        string $verificationIdentifier,
        string $languageIsoCode,
        Request $request
    ): Response {
        $user = $this->userService->getUserForId($userId);
        if (empty($user) || !$user->validateValidationHash($verificationHash)) {
            return new PublicApiControllerUserPasswordCreationFailureResponse();
        }

        $languageIsoCode = in_array(strtoupper($languageIsoCode), Language::getAvailableIsoCodes())
            ? strtolower($languageIsoCode)
            : Core::getConfigValue('defaultSiteLanguage');
        $localisationUtil = Core::getLocalisationUtil($languageIsoCode);

        if (strlen($password) < UserService::USER_PASSWORD_MIN_LENGTH) {
            return new PublicApiControllerUserPasswordCreationSuccessResponse(
                false,
                $localisationUtil->getValue('authenticationPasswordTooShort')
            );
        }

        if ($passwordConfirmation != $password) {
            return new PublicApiControllerUserPasswordCreationSuccessResponse(
                false,
                $localisationUtil->getValue('authenticationPasswordsDoNotMatch')
            );
        }

        $res = $this->userService->workflowCreatePassword(
            user: $user,
            verificationIdentifier: $verificationIdentifier,
            newPassword: $password
        );
        return new PublicApiControllerUserPasswordCreationSuccessResponse($res);
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
