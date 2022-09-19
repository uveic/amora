<?php

namespace Amora\Core\Router;

use Amora\Core\Entity\Response\Pagination;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\mailer\MailerCore;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Value\VerificationType;
use Amora\Core\Router\Controller\Response\PublicApiControllerGetBlogPostsSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerTriggerEmailSenderJobSuccessResponse;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;
use Amora\Core\Value\QueryOrderDirection;
use DateTimeImmutable;
use Throwable;
use Amora\Core\Core;
use Amora\Core\Util\Logger;
use Amora\Core\Module\User\Service\SessionService;
use Amora\Core\Module\User\Service\UserMailService;
use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\App\Value\Language;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Router\Controller\Response\PublicApiControllerUserPasswordResetFailureResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerUserPasswordResetSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerUserRegistrationSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerUserPasswordCreationFailureResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerUserPasswordCreationSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerForgotPasswordSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerGetSessionSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerLogErrorSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerPingSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerRequestRegistrationInviteFailureResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerRequestRegistrationInviteSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerUserLoginSuccessResponse;

final class PublicApiController extends PublicApiControllerAbstract
{
    public function __construct(
        private readonly Logger $logger,
        private readonly UserService $userService,
        private readonly SessionService $sessionService,
        private readonly UserMailService $mailService,
        private readonly ArticleService $articleService,
    ) {
        parent::__construct();
    }

    public function authenticate(Request $request): bool
    {
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
        } catch (Throwable) {
            // Ignore and move on
        }

        return new PublicApiControllerLogErrorSuccessResponse();
    }

    /**
     * Endpoint: /papi/email
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function triggerEmailSenderJob(Request $request): Response
    {
        MailerCore::getMailerApp(isPersistent: false)->run();

        return new PublicApiControllerTriggerEmailSenderJobSuccessResponse();
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
        $user = $request->session?->user;

        $userArray = [];
        if ($user) {
            $userArray = $user->asArray();
            $userArray['language_name'] = $user->language->name;
            $userArray['role_name'] = $user->role->name;
            $userArray['journey_status_name'] = $user->journeyStatus->name;
            unset($userArray['password_hash']);
        }

        return new PublicApiControllerGetSessionSuccessResponse(
            user: $userArray,
            session: $request->session?->asArray(),
        );
    }

    /**
     * Endpoint: /papi/login
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
        $languageIsoCode = strtoupper($languageIsoCode);
        $language = Language::tryFrom($languageIsoCode)
            ? Language::from($languageIsoCode)
            : Core::getDefaultLanguage();
        $localisationUtil = Core::getLocalisationUtil($language, false);

        $user = $this->userService->verifyUser($user, $password);
        if (empty($user)) {
            return new PublicApiControllerUserLoginSuccessResponse(
                success: false,
                redirect: null,
                errorMessage: $localisationUtil->getValue('authenticationEmailAndOrPassNotValid'),
            );
        }

        $session = $this->sessionService->login(
            user: $user,
            timezone: $user->timezone,
            ip: $request->sourceIp,
            userAgent: $request->userAgent,
        );

        if (empty($session)) {
            return new PublicApiControllerUserLoginSuccessResponse(
                success: false,
                redirect: null,
                errorMessage: $localisationUtil->getValue('authenticationEmailAndOrPassNotValid'),
            );
        }

        return new PublicApiControllerUserLoginSuccessResponse(
            success: true,
            redirect: $session->isAdmin()
                ? UrlBuilderUtil::buildBackofficeDashboardUrl($language)
                : UrlBuilderUtil::buildAppDashboardUrl($language),
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
        if (empty($existingUser) || !$existingUser->isEnabled) {
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
        $languageIsoCode = strtoupper($languageIsoCode);
        $language = Language::tryFrom($languageIsoCode)
            ? Language::from($languageIsoCode)
            : Core::getDefaultLanguage();
        $localisationUtil = Core::getLocalisationUtil($language, false);

        try {
            if (!Core::getConfig()->isRegistrationEnabled) {
                return new PublicApiControllerUserRegistrationSuccessResponse(
                    success: false,
                    redirect: null,
                    errorMessage: $localisationUtil->getValue('authenticationUserRegistrationDisabled'),
                );
            }

            $email = StringUtil::normaliseEmail($email);
            if (!StringUtil::isEmailAddressValid($email)) {
                return new PublicApiControllerUserRegistrationSuccessResponse(
                    success: false,
                    redirect: null,
                    errorMessage: $localisationUtil->getValue('authenticationEmailNotValid'),
                );
            }

            if (strlen($password) < UserService::USER_PASSWORD_MIN_LENGTH) {
                return new PublicApiControllerUserRegistrationSuccessResponse(
                    success: false,
                    redirect: null,
                    errorMessage: $localisationUtil->getValue('authenticationPasswordTooShort'),
                );
            }

            $existingUser =$this->userService->getUserForEmail($email);
            if (!empty($existingUser)) {
                return new PublicApiControllerUserRegistrationSuccessResponse(
                    success: false,
                    redirect: null,
                    errorMessage: sprintf(
                        $localisationUtil->getValue('authenticationRegistrationErrorExistingEmail'),
                        UrlBuilderUtil::buildPublicLoginUrl($language)
                    ),
                );
            }

            $now = new DateTimeImmutable();
            $user = $this->userService->storeUser(
                user: new User(
                    id: null,
                    language: Language::from($languageIsoCode),
                    role: UserRole::User,
                    journeyStatus: UserJourneyStatus::Registration,
                    createdAt: $now,
                    updatedAt: $now,
                    email: $email,
                    name: $name,
                    passwordHash: StringUtil::hashPassword($password),
                    bio: null,
                    isEnabled: true,
                    verified: false,
                    timezone: DateUtil::convertStringToDateTimeZone(DateUtil::getTimezoneFromUtcOffset($timezoneOffsetMinutes)),
                ),
                verificationType: VerificationType::EmailAddress,
            );
            $res = !empty($user);

            $session = $this->sessionService->login(
                user: $user,
                timezone: $user->timezone,
                ip: $request->sourceIp,
                userAgent: $request->userAgent,
            );

            return new PublicApiControllerUserRegistrationSuccessResponse(
                success: $res,
                redirect: $session->isAdmin()
                    ? UrlBuilderUtil::buildBackofficeDashboardUrl($language)
                    : UrlBuilderUtil::buildAppDashboardUrl($language),
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
        $languageIsoCode = strtoupper($languageIsoCode);
        $language = Language::tryFrom($languageIsoCode)
            ? Language::from($languageIsoCode)
            : Core::getDefaultLanguage();
        $localisationUtil = Core::getLocalisationUtil($language, false);

        $user = $this->userService->getUserForId($userId);
        if (empty($user) || !$user->validateValidationHash($verificationHash)) {
            return new PublicApiControllerUserPasswordResetFailureResponse();
        }

        if (strlen($password) < UserService::USER_PASSWORD_MIN_LENGTH) {
            return new PublicApiControllerUserPasswordResetSuccessResponse(
                success: false,
                errorMessage: $localisationUtil->getValue('authenticationPasswordTooShort'),
            );
        }

        if ($passwordConfirmation != $password) {
            return new PublicApiControllerUserPasswordResetSuccessResponse(
                success: false,
                errorMessage: $localisationUtil->getValue('authenticationPasswordsDoNotMatch'),
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

        $languageIsoCode = strtoupper($languageIsoCode);
        $language = Language::tryFrom($languageIsoCode)
            ? Language::from($languageIsoCode)
            : Core::getDefaultLanguage();
        $localisationUtil = Core::getLocalisationUtil($language, false);

        if (strlen($password) < UserService::USER_PASSWORD_MIN_LENGTH) {
            return new PublicApiControllerUserPasswordCreationSuccessResponse(
                success: false,
                errorMessage: $localisationUtil->getValue('authenticationPasswordTooShort'),
            );
        }

        if ($passwordConfirmation != $password) {
            return new PublicApiControllerUserPasswordCreationSuccessResponse(
                success: false,
                errorMessage: $localisationUtil->getValue('authenticationPasswordsDoNotMatch'),
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
        $isInvitationEnabled = Core::getConfig()->isInvitationEnabled;
        if (!$isInvitationEnabled) {
            return new PublicApiControllerRequestRegistrationInviteFailureResponse();
        }

        if (!Language::tryFrom(strtoupper($languageIsoCode))) {
            return new PublicApiControllerRequestRegistrationInviteFailureResponse();
        }

        $res = $this->userService->storeRegistrationInviteRequest(
            email: $email,
            language: Language::from(strtoupper($languageIsoCode)),
        );

        return new PublicApiControllerRequestRegistrationInviteSuccessResponse((bool)$res);
    }

    /**
     * Endpoint: /papi/blog/post
     * Method: GET
     *
     * @param int $offset
     * @param int|null $itemsPerPage
     * @param Request $request
     * @return Response
     */
    protected function getBlogPosts(
        int $offset,
        ?int $itemsPerPage,
        Request $request
    ): Response {
        $statusIds = $request->session && $request->session->isAdmin()
            ? [ArticleStatus::Published->value, ArticleStatus::Private->value]
            : [ArticleStatus::Published->value];
        $pagination = new Pagination(itemsPerPage: $itemsPerPage, offset: $offset);
        $articles = $this->articleService->filterArticlesBy(
            statusIds: $statusIds,
            typeIds: [ArticleType::Blog->value],
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy(field: 'published_at', direction: QueryOrderDirection::DESC)],
                pagination: $pagination,
            ),
        );

        $output = [];
        /** @var Article $article */
        foreach ($articles as $article) {
            $output[] = [
                'icon' => ArticleEditHtmlGenerator::generateArticlePublishedIconHtml($article),
                'path' => UrlBuilderUtil::buildPublicArticlePath(path: $article->path),
                'title' => $article->title,
                'publishedOn' => $article->publishOn?->format('c'),
            ];
        }

        return new PublicApiControllerGetBlogPostsSuccessResponse(
            success: true,
            blogPosts: $output,
            pagination: (new Pagination(
                itemsPerPage: $itemsPerPage,
                offset: $offset + count($output),
            ))->asArray(),
        );
    }
}
