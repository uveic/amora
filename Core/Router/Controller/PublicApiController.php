<?php

namespace Amora\Core\Router;

use Amora\Core\Entity\Response\Pagination;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Model\Collection;
use Amora\Core\Module\Album\Service\AlbumService;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Value\UserStatus;
use Amora\Core\Module\User\Value\VerificationType;
use Amora\Core\Router\Controller\Response\PublicApiControllerForgotPasswordSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerGetBlogPostsSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerGetSearchResultsSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerLogCspErrorsSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerLogMessageSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerPingSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerRequestRegistrationInviteSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerUserLoginSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerUserPasswordCreationSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerUserPasswordResetSuccessResponse;
use Amora\Core\Router\Controller\Response\PublicApiControllerUserRegistrationSuccessResponse;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;
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

readonly class PublicApiController extends PublicApiControllerAbstract
{
    public function __construct(
        private Logger $logger,
        private UserService $userService,
        private SessionService $sessionService,
        private UserMailService $mailService,
        private ArticleService $articleService,
        private AlbumService $albumService,
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
     * @param bool $isError
     * @param string|null $endpoint
     * @param string|null $method
     * @param string|null $payload
     * @param string|null $errorMessage
     * @param string|null $userAgent
     * @param string|null $pageUrl
     * @param Request $request
     * @return Response
     */
    protected function logMessage(
        bool $isError,
        ?string $endpoint,
        ?string $method,
        ?string $payload,
        ?string $errorMessage,
        ?string $userAgent,
        ?string $pageUrl,
        Request $request
    ): Response {
        try {
            $logMessage = 'AJAX Logger';

            if ($errorMessage) {
                $logMessage .= ' - Error message: ' . $errorMessage;
            }

            if ($method || $endpoint) {
                $logMessage .= ' - Endpoint: ' . $method . ' ' . $endpoint;
            }

            if ($payload) {
                $logMessage .= ' - Payload: ' . $payload;
            }

            if ($pageUrl) {
                $logMessage .= ' - Page URL: ' . $pageUrl;
            }

            if ($userAgent) {
                $logMessage .= ' - User agent: ' . $userAgent;
            }

            if ($isError) {
                $this->logger->logError($logMessage);
            } else {
                $this->logger->logInfo($logMessage);
            }
        } catch (Throwable) {
            // Ignore and move on
        }

        return new PublicApiControllerLogMessageSuccessResponse();
    }

    /**
     * Endpoint: /papi/csp
     * Method: POST
     *
     * @param Request $request
     * @return Response
     */
    protected function logCspErrors(Request $request): Response
    {
        try {
            $this->logger->logError('CSP Errors Logger: ' . $request->body);
        } catch (Throwable) {
            // Ignore and move on
        }

        return new PublicApiControllerLogCspErrorsSuccessResponse();
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

        $userObj = $this->userService->verifyUser(email: $user, unHashedPassword: $password);
        if (!$userObj) {
            return new PublicApiControllerUserLoginSuccessResponse(
                success: false,
                errorMessage: $localisationUtil->getValue('authenticationEmailAndOrPassNotValid'),
            );
        }

        $session = $this->sessionService->login(
            user: $userObj,
            timezone: $userObj->timezone,
            ip: $request->sourceIp,
            userAgent: $request->userAgent,
        );

        if (!$session) {
            return new PublicApiControllerUserLoginSuccessResponse(
                success: false,
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
        $existingUser = $this->userService->getUserForEmail($email);
        if (!$existingUser || !$existingUser->isEnabled()) {
            return new PublicApiControllerForgotPasswordSuccessResponse(true);
        }

        $res = $this->mailService->workflowSendPasswordResetEmail($existingUser);
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
     * @param string $timezone
     * @param Request $request
     * @return Response
     */
    protected function userRegistration(
        string $languageIsoCode,
        string $email,
        string $password,
        string $name,
        string $timezone,
        Request $request
    ): Response {
        $languageIsoCode = strtoupper($languageIsoCode);
        $language = Language::tryFrom($languageIsoCode)
            ? Language::from($languageIsoCode)
            : Core::getDefaultLanguage();
        $localisationUtil = Core::getLocalisationUtil($language, false);

        if (!Core::getConfig()->isRegistrationEnabled) {
            return new PublicApiControllerUserRegistrationSuccessResponse(
                success: false,
                redirect: null,
                errorMessage: $localisationUtil->getValue('authenticationUserRegistrationDisabled'),
            );
        }

        $email = StringUtil::sanitiseText($email);
        $name = StringUtil::sanitiseText($name);
        $timezone = StringUtil::sanitiseText($timezone);

        $email = StringUtil::normaliseEmail($email);
        if (!StringUtil::isEmailAddressValid($email)) {
            return new PublicApiControllerUserRegistrationSuccessResponse(
                success: false,
                redirect: null,
                errorMessage: $localisationUtil->getValue('authenticationEmailNotValid'),
            );
        }

        if (strlen($password) < Core::USER_PASSWORD_MIN_LENGTH) {
            return new PublicApiControllerUserRegistrationSuccessResponse(
                success: false,
                redirect: null,
                errorMessage: $localisationUtil->getValue('authenticationPasswordTooShort'),
            );
        }

        $existingUser = $this->userService->getUserForEmail($email);
        if ($existingUser) {
            return new PublicApiControllerUserRegistrationSuccessResponse(
                success: false,
                redirect: null,
                errorMessage: $localisationUtil->getValue('authenticationRegistrationErrorExistingEmail'),
            );
        }

        $now = new DateTimeImmutable();
        $user = $this->userService->workflowStoreUserAndSendVerificationEmail(
            createdByUser: $request->session?->user,
            user: new User(
                id: null,
                status: UserStatus::Enabled,
                language: Language::from($languageIsoCode),
                role: UserRole::User,
                journeyStatus: UserJourneyStatus::RegistrationComplete,
                createdAt: $now,
                updatedAt: $now,
                email: $email,
                name: $name,
                passwordHash: StringUtil::hashPassword($password),
                bio: null,
                identifier: null,
                timezone: DateUtil::convertStringToDateTimeZone($timezone),
            ),
            verificationType: VerificationType::VerifyEmailAddress,
        );

        if (!$user) {
            $this->logger->logError('Error storing user');
            return new PublicApiControllerUserRegistrationSuccessResponse(
                success: false,
                errorMessage: 'Error storing user',
            );
        }

        $session = $this->sessionService->login(
            user: $user,
            timezone: $user->timezone,
            ip: $request->sourceIp,
            userAgent: $request->userAgent,
        );

        if (!$session) {
            $this->logger->logError('Error storing session');
            return new PublicApiControllerUserRegistrationSuccessResponse(
                success: false,
                errorMessage: 'Error creating session',
            );
        }

        return new PublicApiControllerUserRegistrationSuccessResponse(
            success: true,
            redirect: $session->isAdmin()
                ? UrlBuilderUtil::buildBackofficeDashboardUrl($language)
                : UrlBuilderUtil::buildAppDashboardUrl($language),
        );
    }

    /**
     * Endpoint: /papi/login/password-reset
     * Method: POST
     *
     * @param int $userId
     * @param string $password
     * @param string $passwordConfirmation
     * @param string $validationHash
     * @param string $verificationIdentifier
     * @param string $languageIsoCode
     * @param Request $request
     * @return Response
     */
    protected function userPasswordReset(
        int $userId,
        string $password,
        string $passwordConfirmation,
        string $validationHash,
        string $verificationIdentifier,
        string $languageIsoCode,
        Request $request
    ): Response {
        $languageIsoCode = strtoupper($languageIsoCode);
        $language = Language::tryFrom($languageIsoCode)
            ? Language::from($languageIsoCode)
            : Core::getDefaultLanguage();
        $localisationUtil = Core::getLocalisationUtil($language, false);

        $user = $this->userService->getUserForId($userId);
        if (!$user || !$user->validateValidationHash($validationHash)) {
            return new PublicApiControllerUserPasswordResetSuccessResponse(
                success: false,
                errorMessage: 'User not found',
            );
        }

        $verification = $this->userService->getUserVerification(
            verificationIdentifier: $verificationIdentifier,
            type: VerificationType::PasswordReset,
            isEnabled: true,
        );

        if (!$verification) {
            return new PublicApiControllerUserPasswordResetSuccessResponse(
                success: false,
                errorMessage: 'Verification not found',
            );
        }

        if (strlen($password) < Core::USER_PASSWORD_MIN_LENGTH) {
            return new PublicApiControllerUserPasswordResetSuccessResponse(
                success: false,
                errorMessage: $localisationUtil->getValue('authenticationPasswordTooShort'),
            );
        }

        if ($passwordConfirmation !== $password) {
            return new PublicApiControllerUserPasswordResetSuccessResponse(
                success: false,
                errorMessage: $localisationUtil->getValue('authenticationPasswordsDoNotMatch'),
            );
        }

        $res = $this->userService->workflowUpdatePassword(
            updatedByUser: $request->session?->user ?? $user,
            userId: $userId,
            newPassword: $password,
            verification: $verification,
        );
        return new PublicApiControllerUserPasswordResetSuccessResponse($res);
    }

    /**
     * Endpoint: /papi/login/password-creation
     * Method: POST
     *
     * @param int $userId
     * @param string $password
     * @param string $passwordConfirmation
     * @param string $validationHash
     * @param string $verificationIdentifier
     * @param string $languageIsoCode
     * @param Request $request
     * @return Response
     */
    protected function userPasswordCreation(
        int $userId,
        string $password,
        string $passwordConfirmation,
        string $validationHash,
        string $verificationIdentifier,
        string $languageIsoCode,
        Request $request
    ): Response {
        $user = $this->userService->getUserForId($userId);
        if (!$user || !$user->validateValidationHash($validationHash)) {
            return new PublicApiControllerUserPasswordCreationSuccessResponse(
                success: false,
                errorMessage: 'User not found',
            );
        }

        $languageIsoCode = strtoupper($languageIsoCode);
        $language = Language::tryFrom($languageIsoCode)
            ? Language::from($languageIsoCode)
            : Core::getDefaultLanguage();
        $localisationUtil = Core::getLocalisationUtil($language, false);

        if (strlen($password) < Core::USER_PASSWORD_MIN_LENGTH) {
            return new PublicApiControllerUserPasswordCreationSuccessResponse(
                success: false,
                errorMessage: $localisationUtil->getValue('authenticationPasswordTooShort'),
            );
        }

        if ($passwordConfirmation !== $password) {
            return new PublicApiControllerUserPasswordCreationSuccessResponse(
                success: false,
                errorMessage: $localisationUtil->getValue('authenticationPasswordsDoNotMatch'),
            );
        }

        $res = $this->userService->workflowCreatePassword(
            updatedByUser: $request->session?->user ?? $user,
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
            return new PublicApiControllerRequestRegistrationInviteSuccessResponse(
                success: false,
            );
        }

        if (!Language::tryFrom(strtoupper($languageIsoCode))) {
            return new PublicApiControllerRequestRegistrationInviteSuccessResponse(
                success: false,
                errorMessage: 'Language not found',
            );
        }

        $email = StringUtil::sanitiseText($email);
        $email = StringUtil::normaliseEmail($email);

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
            ? [ArticleStatus::Published->value, ArticleStatus::Unlisted->value, ArticleStatus::Private->value]
            : [ArticleStatus::Published->value];
        $pagination = new Pagination(itemsPerPage: $itemsPerPage, offset: $offset);
        $articles = $this->articleService->filterArticleBy(
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
                'icon' => ArticleHtmlGenerator::generateArticlePublishedIconHtml($article),
                'path' => UrlBuilderUtil::buildPublicArticlePath(path: $article->path),
                'title' => $article->title,
                'publishedOn' => $article->publishOn?->format('c'),
            ];
        }

        return new PublicApiControllerGetBlogPostsSuccessResponse(
            success: true,
            blogPosts: $output,
            pagination: new Pagination(
                itemsPerPage: $itemsPerPage,
                offset: $offset + count($output),
            )->asArray(),
        );
    }

    /**
     * Endpoint: /papi/search
     * Method: GET
     *
     * @param string $q Query string
     * @param string|null $isPublic Is a public page?
     * @param int|null $searchTypeId
     * @param Request $request
     * @return Response
     */
    protected function getSearchResults(string $q, ?string $isPublic, ?int $searchTypeId, Request $request): Response
    {
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);

        $albumStatusIds = [
            AlbumStatus::Published->value,
            AlbumStatus::Unlisted->value,
        ];

        if ($request->session->isAdmin()) {
            $albumStatusIds[] = AlbumStatus::Private->value;
        }

        $albums = $this->albumService->filterAlbumBy(
            statusIds: $albumStatusIds,
            searchQuery: $q,
            queryOptions: new QueryOptions(
                orderBy: [
                    new QueryOrderBy('begins_with', QueryOrderDirection::DESC),
                    new QueryOrderBy('word_begins_with', QueryOrderDirection::DESC),
                    new QueryOrderBy('title_contains', QueryOrderDirection::DESC),
                    new QueryOrderBy('updated_at', QueryOrderDirection::DESC),
                ],
                pagination: new Response\Pagination(
                    itemsPerPage: 10,
                ),
            ),
        );

        $existingAlbumIds = [];
        $albumsOutput = [];
        /** @var Album $album */
        foreach ($albums as $album) {
            $existingAlbumIds[$album->id] = true;
            $albumsOutput[] = $album->asSearchResult(
                language: $request->siteLanguage,
                isPublicUrl: $isPublic,
            )->asPublicArray($localisationUtil->getValue('navAdminAlbums'));
        }

        $collections = $this->albumService->filterCollectionBy(
            searchQuery: $q,
            queryOptions: new QueryOptions(
                orderBy: [
                    new QueryOrderBy('begins_with', QueryOrderDirection::DESC),
                    new QueryOrderBy('word_begins_with', QueryOrderDirection::DESC),
                    new QueryOrderBy('title_contains', QueryOrderDirection::DESC),
                ],
                pagination: new Response\Pagination(
                    itemsPerPage: 10,
                ),
            ),
        );

        $albumIds = [];
        /** @var Collection $collection */
        foreach ($collections as $collection) {
            $albumIds[] = $collection->albumId;
        }

        $totalAlbums = count($albumsOutput);
        if ($albumIds && $totalAlbums <= 10) {
            $moreAlbums = $this->albumService->filterAlbumBy(
                albumIds: $albumIds,
                statusIds: $albumStatusIds,
            );

            /** @var Album $album */
            foreach ($moreAlbums as $album) {
                if (isset($existingAlbumIds[$album->id])) {
                    continue;
                }

                $existingAlbumIds[$album->id] = true;
                ++$totalAlbums;
                $albumsOutput[] = $album->asSearchResult(
                    language: $request->siteLanguage,
                    isPublicUrl: $isPublic,
                )->asPublicArray($localisationUtil->getValue('navAdminAlbums'));

                if ($totalAlbums >= 10) {
                    break;
                }
            }
        }

        $articleStatusIds = [
            ArticleStatus::Published->value,
            ArticleStatus::Unlisted->value,
        ];

        if ($request->session->isAdmin()) {
            $articleStatusIds[] = ArticleStatus::Private->value;
        }

        $articles = $this->articleService->filterArticleBy(
            statusIds: $articleStatusIds,
            searchQuery: $q,
            queryOptions: new QueryOptions(
                orderBy: [
                    new QueryOrderBy('begins_with', QueryOrderDirection::DESC),
                    new QueryOrderBy('word_begins_with', QueryOrderDirection::DESC),
                    new QueryOrderBy('title_contains', QueryOrderDirection::DESC),
                    new QueryOrderBy('published_at', QueryOrderDirection::DESC),
                    new QueryOrderBy('updated_at', QueryOrderDirection::DESC),
                ],
                pagination: new Response\Pagination(
                    itemsPerPage: 10,
                ),
            ),
        );

        $blogPostsOutput = [];
        $pagesOutput = [];
        /** @var Article $article */
        foreach ($articles as $article) {
            if ($article->type === ArticleType::Blog) {
                $blogPostsOutput[] = $article->asSearchResult(
                    language: $request->siteLanguage,
                    isPublicUrl: $isPublic,
                )->asPublicArray($localisationUtil->getValue('navAdminBlogPosts'));
            } elseif ($article->type === ArticleType::Page) {
                $pagesOutput[] = $article->asSearchResult(
                    language: $request->siteLanguage,
                    isPublicUrl: $isPublic,
                )->asPublicArray($localisationUtil->getValue('navAdminArticles'));
            }
        }

        return new PublicApiControllerGetSearchResultsSuccessResponse(
            success: true,
            results: array_merge($albumsOutput, $pagesOutput, $blogPostsOutput),
        );
    }
}
