<?php

namespace Amora\Core\Router;

use Amora\App\Module\Form\Entity\PageContent;
use Amora\App\Value\AppPageContentType;
use Amora\App\Value\AppUserRole;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Album\Model\AlbumSection;
use Amora\Core\Module\Album\Model\AlbumSectionMedia;
use Amora\Core\Module\Album\Service\AlbumService;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Module\Album\Value\Template;
use Amora\Core\Module\Article\Model\ArticlePath;
use Amora\Core\Module\Article\Service\MediaService;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Module\User\Value\UserStatus;
use Amora\Core\Module\User\Value\VerificationType;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerDeleteAlbumSectionMediaSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerGetSessionSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerStoreAlbumSectionSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerStoreAlbumSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerStoreMediaForAlbumSectionSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerUpdateAlbumSectionMediaSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerUpdateAlbumSectionSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerUpdateAlbumStatusSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerUpdateAlbumSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerUpdateMediaSequenceForAlbumSectionSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerUpdatePageContentSuccessResponse;
use Amora\Core\Util\Helper\AlbumHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\App\Value\Language;
use Amora\Core\Value\QueryOrderDirection;
use DateTimeImmutable;
use Throwable;
use Amora\Core\Core;
use Amora\Core\Util\Logger;
use Amora\Core\Entity\Response;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\Tag;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Service\TagService;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Entity\Request;
use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerDestroyArticleFailureResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerDestroyArticleSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerDestroyUserFailureResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerDestroyUserSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerGetTagsSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerGetUsersSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerStoreArticleSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerStoreTagFailureResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerStoreTagSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerStoreUserSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerUpdateArticleSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerUpdateUserFailureResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerUpdateUserSuccessResponse;

final class BackofficeApiController extends BackofficeApiControllerAbstract
{
    public function __construct(
        private readonly Logger $logger,
        private readonly UserService $userService,
        private readonly ArticleService $articleService,
        private readonly TagService $tagService,
        private readonly MediaService $mediaService,
        private readonly AlbumService $albumService,
    ) {
        parent::__construct();
    }

    public function authenticate(Request $request): bool
    {
        $session = $request->session;
        if (empty($session) || !$session->isAuthenticated() || !$session->isAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Endpoint: /back/session
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

        return new BackofficeApiControllerGetSessionSuccessResponse(
            user: $userArray,
            session: $request->session?->asArray(),
        );
    }

    /**
     * Endpoint: /back/user
     * Method: POST
     *
     * @param string $name
     * @param string $email
     * @param string|null $bio
     * @param string|null $languageIsoCode
     * @param int|null $roleId
     * @param string|null $timezone
     * @param bool|null $isEnabled
     * @param Request $request
     * @return Response
     */
    protected function storeUser(
        string $name,
        string $email,
        ?string $bio,
        ?string $languageIsoCode,
        ?int $roleId,
        ?string $timezone,
        ?bool $isEnabled,
        Request $request
    ): Response {
        $name = StringUtil::sanitiseText($name);
        $email = StringUtil::sanitiseText($email);
        $bio = StringUtil::sanitiseText($bio);
        $timezone = StringUtil::sanitiseText($timezone);

        $now = new DateTimeImmutable();
        $email = StringUtil::normaliseEmail($email);
        $language = $languageIsoCode && Language::tryFrom(strtoupper($languageIsoCode))
            ? Language::from(strtoupper($languageIsoCode))
            : $request->siteLanguage;
        $localisationUtil = Core::getLocalisationUtil($language);

        if (!StringUtil::isEmailAddressValid($email)) {
            return new BackofficeApiControllerStoreUserSuccessResponse(
                success: false,
                errorMessage: $localisationUtil->getValue('authenticationEmailNotValid'),
            );
        }

        $existingUser =$this->userService->getUserForEmail($email);
        if (!empty($existingUser)) {
            return new BackofficeApiControllerStoreUserSuccessResponse(
                success: false,
                errorMessage: $localisationUtil->getValue('authenticationRegistrationErrorExistingEmail'),
            );
        }

        if ($roleId) {
            $userRole = UserRole::tryFrom($roleId)
                ? UserRole::from($roleId)
                : (AppUserRole::tryFrom($roleId) ? AppUserRole::from($roleId) : UserRole::User);
        } else {
            $userRole = UserRole::User;
        }

        $timezone = $timezone
            ? DateUtil::convertStringToDateTimeZone($timezone)
            : $request->session->user->timezone;
        $isEnabled = $isEnabled ?? true;

        try {
            $newUser = $this->userService->storeUser(
                user: new User(
                    id: null,
                    status: $isEnabled ? UserStatus::Enabled : UserStatus::Disabled,
                    language: $language,
                    role: $userRole,
                    journeyStatus: UserJourneyStatus::PendingPasswordCreation,
                    createdAt: $now,
                    updatedAt: $now,
                    email: $email,
                    name: $name,
                    passwordHash: null,
                    bio: $bio,
                    timezone: $timezone,
                ),
                verificationType: VerificationType::PasswordCreation,
            );
        } catch (Throwable $t) {
            $this->logger->logError('Error creating new user: ' . $t->getMessage());
            return new BackofficeApiControllerStoreUserSuccessResponse(
                success: false,
                errorMessage: $localisationUtil->getValue('globalGenericError'),
            );
        }

        return new BackofficeApiControllerStoreUserSuccessResponse(
            success: true,
            id: $newUser?->id,
            redirect: UrlBuilderUtil::buildBackofficeUserListUrl($request->siteLanguage),
        );
    }

    /**
     * Endpoint: /back/user
     * Method: GET
     *
     * @param string|null $q
     * @param Request $request
     * @return Response
     */
    protected function getUsers(?string $q, Request $request): Response
    {
        $q = StringUtil::sanitiseText($q);

        $users = $this->userService->filterUserBy(
            searchText: $q,
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy(field: 'name', direction: QueryOrderDirection::ASC)],
                pagination: new Response\Pagination(itemsPerPage: 25),
            ),
        );

        $output = [];
        /** @var User $user */
        foreach ($users as $user) {
            $output[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        }

        return new BackofficeApiControllerGetUsersSuccessResponse(
            success: true,
            users: $output,
        );
    }

    /**
     * Endpoint: /back/user/{userId}
     * Method: PUT
     *
     * @param int $userId
     * @param string|null $name
     * @param string|null $email
     * @param string|null $bio
     * @param string|null $languageIsoCode
     * @param int|null $roleId
     * @param string|null $timezone
     * @param int|null $userStatusId
     * @param string|null $currentPassword
     * @param string|null $newPassword
     * @param string|null $repeatPassword
     * @param Request $request
     * @return Response
     */
    public function updateUser(
        int $userId,
        ?string $name,
        ?string $email,
        ?string $bio,
        ?string $languageIsoCode,
        ?int $roleId,
        ?string $timezone,
        ?int $userStatusId,
        ?string $currentPassword,
        ?string $newPassword,
        ?string $repeatPassword,
        Request $request
    ): Response {
        $existingUser = $this->userService->getUserForId($userId, true);
        if (empty($existingUser)) {
            return new BackofficeApiControllerUpdateUserFailureResponse();
        }

        if ($userStatusId && !UserStatus::tryFrom($userStatusId)) {
            return new BackofficeApiControllerUpdateUserSuccessResponse(
                success: false,
                redirect: null,
                errorMessage: 'User status ID not valid',
            );
        }

        if ($roleId) {
            $userRole = UserRole::tryFrom($roleId)
                ? UserRole::from($roleId)
                : (AppUserRole::tryFrom($roleId) ? AppUserRole::from($roleId) : UserRole::User);
        } else {
            $userRole = $existingUser->role;
        }

        $updateRes = $this->userService->workflowUpdateUser(
            existingUser: $existingUser,
            name: $name,
            email: $email,
            bio: $bio,
            languageIsoCode: $languageIsoCode,
            timezone: $timezone,
            currentPassword: $currentPassword,
            newPassword: $newPassword,
            repeatPassword: $repeatPassword,
            userStatus: $userStatusId ? UserStatus::from($userStatusId) : null,
            userRole: $userRole,
        );

        if (!$updateRes->isSuccess) {
            return new BackofficeApiControllerUpdateUserSuccessResponse(
                success: false,
                redirect: null,
                errorMessage: $updateRes->message,
            );
        }

        return new BackofficeApiControllerUpdateUserSuccessResponse(
            success: true,
            redirect: UrlBuilderUtil::buildBackofficeUserListUrl($request->siteLanguage),
        );
    }

    /**
     * Endpoint: /backoffice/users/{userId}
     * Method: DELETE
     *
     * @param int $userId
     * @param Request $request
     * @return Response
     */
    protected function destroyUser(int $userId, Request $request): Response
    {
        $user = $this->userService->getUserForId($userId, true);
        if (empty($user)) {
            return new BackofficeApiControllerDestroyUserFailureResponse();
        }

        $res = $this->userService->deleteUser($user);

        return new BackofficeApiControllerDestroyUserSuccessResponse(
            success: $res,
            errorMessage: $res
                ? null
                : Core::getLocalisationUtil($request->siteLanguage)->getValue('globalGenericError')
        );
    }

    /**
     * Endpoint: /back/article
     * Method: POST
     *
     * @param string $siteLanguageIsoCode
     * @param string $articleLanguageIsoCode
     * @param int $statusId
     * @param int $typeId
     * @param string|null $title
     * @param string $contentHtml
     * @param int|null $mainImageId
     * @param string|null $publishOn
     * @param array|null $mediaIds
     * @param array $sections
     * @param array|null $tags
     * @param Request $request
     * @return Response
     */
    public function storeArticle(
        string $siteLanguageIsoCode,
        string $articleLanguageIsoCode,
        int $statusId,
        int $typeId,
        ?string $title,
        string $contentHtml,
        ?int $mainImageId,
        ?string $publishOn,
        ?array $mediaIds,
        array $sections,
        ?array $tags,
        Request $request
    ): Response {
        if (!ArticleType::tryFrom($typeId)) {
            return new BackofficeApiControllerStoreArticleSuccessResponse(
                success: false,
                errorMessage: 'Invalid article type',
            );
        }

        if (!ArticleStatus::tryFrom($statusId) === null) {
            return new BackofficeApiControllerStoreArticleSuccessResponse(
                success: false,
                errorMessage: 'Invalid article status',
            );
        }

        if (!Language::tryFrom(strtoupper($articleLanguageIsoCode))) {
            return new BackofficeApiControllerStoreArticleSuccessResponse(
                success: false,
                errorMessage: 'Invalid article language',
            );
        }

        if (!Language::tryFrom(strtoupper($siteLanguageIsoCode))) {
            return new BackofficeApiControllerStoreArticleSuccessResponse(
                success: false,
                errorMessage: 'Invalid site language',
            );
        }

        $title = StringUtil::sanitiseText($title);
        $contentHtml = StringUtil::sanitiseHtml(html_entity_decode($contentHtml));

        $now = new DateTimeImmutable();
        $articleLanguage = Language::from(strtoupper($articleLanguageIsoCode));
        $status = ArticleStatus::from($statusId);
        $path = $this->articleService->getAvailablePathForArticle(
            articleTitle: $title,
            articleStatus: $status,
        );
        $publishOn = $publishOn
            ? DateUtil::convertStringToDateTimeImmutable($publishOn)
            : ($status->isPublic() ? $now : null);
        $existingMedia = $this->mediaService->filterMediaBy(ids: $mediaIds);

        $newArticle = $this->articleService->createNewArticle(
            article: new Article(
                id: null,
                language: $articleLanguage,
                user: $request->session->user,
                status: $status,
                type: $typeId ? ArticleType::from($typeId) : ArticleType::Page,
                createdAt: $now,
                updatedAt: $now,
                publishOn: $publishOn,
                title: $title,
                contentHtml: $contentHtml,
                mainImageId: $mainImageId,
                mainImage: null,
                path: $path,
                tags: [],
            ),
            sections: $sections,
            media: $existingMedia,
            tags: $tags ?? [],
            userIp: $request->sourceIp,
            userAgent: $request->userAgent,
        );

        if (empty($newArticle)) {
            return new BackofficeApiControllerStoreArticleSuccessResponse(
                success: false,
                errorMessage: 'Failed to create article',
            );
        }

        $siteLanguage = Language::from(strtoupper($siteLanguageIsoCode));
        $articlePublicUrl = UrlBuilderUtil::buildPublicArticlePath($newArticle->path, $articleLanguage);
        return new BackofficeApiControllerStoreArticleSuccessResponse(
            success: (bool)$newArticle,
            articleId: $newArticle->id,
            articleBackofficePath: UrlBuilderUtil::buildBackofficeArticleUrl($siteLanguage, $newArticle->id),
            articleBackofficePathPreview: UrlBuilderUtil::buildBackofficeArticlePreviewUrl(
                language: $siteLanguage,
                articleId: $newArticle->id,
            ),
            articlePublicUrlHtml: $newArticle->status->isPublic() ?
                '<a href="' . $articlePublicUrl . '">' . $articlePublicUrl . '</a>'
                : $articlePublicUrl,
        );
    }

    /**
     * Endpoint: /back/article/{articleId}
     * Method: PUT
     *
     * @param int $articleId
     * @param string $siteLanguageIsoCode
     * @param string $articleLanguageIsoCode
     * @param int $statusId
     * @param int $typeId
     * @param string|null $title
     * @param string $contentHtml
     * @param int|null $mainImageId
     * @param string|null $publishOn
     * @param array $mediaIds
     * @param array $sections
     * @param array|null $tags
     * @param Request $request
     * @return Response
     */
    public function updateArticle(
        int $articleId,
        string $siteLanguageIsoCode,
        string $articleLanguageIsoCode,
        int $statusId,
        int $typeId,
        ?string $title,
        string $contentHtml,
        ?int $mainImageId,
        ?string $publishOn,
        array $mediaIds,
        array $sections,
        ?array $tags,
        Request $request
    ): Response {
        if (!ArticleType::tryFrom($typeId)) {
            return new BackofficeApiControllerUpdateArticleSuccessResponse(
                success: false,
                errorMessage: 'Invalid article type',
            );
        }

        if (!ArticleStatus::tryFrom($statusId) === null) {
            return new BackofficeApiControllerUpdateArticleSuccessResponse(
                success: false,
                errorMessage: 'Invalid article status',
            );
        }

        if (!Language::tryFrom(strtoupper($articleLanguageIsoCode))) {
            return new BackofficeApiControllerUpdateArticleSuccessResponse(
                success: false,
                errorMessage: 'Invalid article language',
            );
        }

        if (!Language::tryFrom(strtoupper($siteLanguageIsoCode))) {
            return new BackofficeApiControllerUpdateArticleSuccessResponse(
                success: false,
                errorMessage: 'Invalid site language',
            );
        }

        $existingArticle = $this->articleService->getArticleForId(
            id: $articleId,
        );

        if (!$existingArticle) {
            return new BackofficeApiControllerUpdateArticleSuccessResponse(
                success: false,
                errorMessage: 'Article not found',
            );
        }

        $title = StringUtil::sanitiseText($title);
        $contentHtml = html_entity_decode($contentHtml);
        $contentHtml = StringUtil::sanitiseHtml($contentHtml);

        $status = $statusId ? ArticleStatus::from($statusId) : $existingArticle->status;
        $path = $this->articleService->getAvailablePathForArticle(
            articleTitle: $title,
            existingArticle: $existingArticle,
            articleStatus: $status,
        );

        if ($path !== $existingArticle->path) {
            $this->articleService->storeArticlePath(
                new ArticlePath(
                    id: null,
                    articleId: $existingArticle->id,
                    path: $existingArticle->path,
                    createdAt: new DateTimeImmutable(),
                )
            );
        }

        $now = new DateTimeImmutable();
        $publishOn = $publishOn
            ? DateUtil::convertStringToDateTimeImmutable($publishOn)
            : ($existingArticle->publishOn ?? $now);

        $articleLanguage = Language::from(strtoupper($articleLanguageIsoCode));
        $type = $typeId ? ArticleType::from($typeId) : $existingArticle->type;
        $existingMedia = $this->mediaService->filterMediaBy(ids: $mediaIds);
        $res = $this->articleService->workflowUpdateArticle(
            article: new Article(
                id: $articleId,
                language: $articleLanguage,
                user: $request->session->user,
                status: $status,
                type: $type,
                createdAt: $existingArticle->createdAt,
                updatedAt: $now,
                publishOn: $publishOn,
                title: $title ?? $existingArticle->title,
                contentHtml: $contentHtml ?? $existingArticle->contentHtml,
                mainImageId: $mainImageId ?? $existingArticle->mainImage?->id,
                mainImage: null,
                path: $path
            ),
            mediaIds: $mediaIds,
            media: $existingMedia,
            sections: $sections,
            tags: $tags ?? [],
            userIp: $request->sourceIp,
            userAgent: $request->userAgent,
        );

        $siteLanguage = Language::from(strtoupper($siteLanguageIsoCode));
        $articlePublicUrl = UrlBuilderUtil::buildPublicArticlePath($path, $articleLanguage);
        return new BackofficeApiControllerUpdateArticleSuccessResponse(
            success: $res,
            articleId: $res ? $articleId : null,
            articleBackofficePath: UrlBuilderUtil::buildBackofficeArticleUrl(
                language: $siteLanguage,
                articleId: $articleId,
            ),
            articleBackofficePathPreview: UrlBuilderUtil::buildBackofficeArticlePreviewUrl(
                language: $siteLanguage,
                articleId: $articleId,
            ),
            articlePublicUrlHtml: $status->isPublic() ?
                '<a href="' . $articlePublicUrl . '">' . $articlePublicUrl . '</a>'
                : $articlePublicUrl,
        );
    }

    /**
     * Endpoint: /back/article/{articleId}
     * Method: DELETE
     *
     * @param int $articleId
     * @param Request $request
     * @return Response
     */
    public function destroyArticle(int $articleId, Request $request): Response
    {
        $existingArticle = $this->articleService->getArticleForId($articleId);
        if (empty($existingArticle)) {
            return new BackofficeApiControllerDestroyArticleFailureResponse();
        }

        $deleteRes = $this->articleService->deleteArticle(
            article: new Article(
                id: $existingArticle->id,
                language: $existingArticle->language,
                user: $existingArticle->user,
                status: ArticleStatus::Deleted,
                type: $existingArticle->type,
                createdAt: $existingArticle->createdAt,
                updatedAt: new DateTimeImmutable(),
                publishOn: $existingArticle->publishOn,
                title: $existingArticle->title,
                contentHtml: $existingArticle->contentHtml,
                mainImageId: $existingArticle->mainImage?->id,
                mainImage: $existingArticle->mainImage,
                path: $existingArticle->path,
            ),
            userIp: $request->sourceIp,
            userAgent: $request->userAgent,
        );

        return new BackofficeApiControllerDestroyArticleSuccessResponse($deleteRes);
    }

    /**
     * Endpoint: /back/tag
     * Method: POST
     *
     * @param string $name
     * @param Request $request
     * @return Response
     */
    protected function storeTag(string $name, Request $request): Response
    {
        $name = StringUtil::sanitiseText($name);

        $existingTag = $this->tagService->getTagForName($name);
        if ($existingTag) {
            return new BackofficeApiControllerStoreTagSuccessResponse(
                success: true,
                id: $existingTag->id,
            );
        }

        $res = $this->tagService->storeTag(new Tag(null, $name));

        if (empty($res)) {
            return new BackofficeApiControllerStoreTagFailureResponse();
        }

        return new BackofficeApiControllerStoreTagSuccessResponse(
            success: true,
            id: $res->id,
        );
    }

    /**
     * Endpoint: /back/tag
     * Method: GET
     *
     * @param string|null $name
     * @param Request $request
     * @return Response
     */
    protected function getTags(?string $name, Request $request): Response
    {
//        $name = StringUtil::sanitiseText($name);

        $tags = $this->tagService->filterTagsBy();
        $output = [];
        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $output[] = $tag->asArray();
        }

        return new BackofficeApiControllerGetTagsSuccessResponse(
            success: true,
            tags: $output,
        );
    }

    /**
     * Endpoint: /back/content/{contentId}
     * Method: PUT
     *
     * @param int $contentId
     * @param string|null $title
     * @param string|null $subtitle
     * @param string|null $contentHtml
     * @param int|null $mainImageId
     * @param string|null $actionUrl
     * @param Request $request
     * @return Response
     */
    protected function updatePageContent(
        int $contentId,
        ?string $title,
        ?string $subtitle,
        ?string $contentHtml,
        ?int $mainImageId,
        ?string $actionUrl,
        Request $request
    ): Response {
        $existingPageContent = $this->articleService->getPageContentForId($contentId);
        if (!$existingPageContent) {
            return new BackofficeApiControllerUpdatePageContentSuccessResponse(
                success: false,
                redirect: null,
                errorMessage: 'Page content ID not found',
            );
        }

        $title = StringUtil::sanitiseText($title);
        $subtitle = StringUtil::sanitiseText($subtitle);
        $contentHtml = StringUtil::sanitiseHtml($contentHtml);

        $existingImage = null;
        if ($mainImageId) {
            $existingImage = $this->mediaService->getMediaForId($mainImageId);
            if (!$existingImage) {
                return new BackofficeApiControllerUpdatePageContentSuccessResponse(
                    success: false,
                    redirect: null,
                    errorMessage: 'Main image ID not found',
                );
            }
        }

        $pageContent = new PageContent(
            id: $existingPageContent->id,
            user: $request->session->user,
            language: $existingPageContent->language,
            type: $existingPageContent->type,
            createdAt: $existingPageContent->createdAt,
            updatedAt: new DateTimeImmutable(),
            title: $title,
            subtitle: $subtitle,
            contentHtml: $contentHtml,
            mainImage: $existingImage,
            actionUrl: $actionUrl,
        );

        $resUpdate = $this->articleService->updatePageContent($pageContent);

        return new BackofficeApiControllerUpdatePageContentSuccessResponse(
            success: $resUpdate,
            redirect: AppPageContentType::buildRedirectUrl(
                type: $pageContent->type,
                language: $pageContent->language,
            ),
            errorMessage: $resUpdate ? null : 'Error updating page content',
        );
    }

    /**
     * Endpoint: /back/album
     * Method: POST
     *
     * @param string|null $languageIsoCode
     * @param int $mainMediaId
     * @param int $templateId
     * @param string $titleHtml
     * @param string|null $contentHtml
     * @param Request $request
     * @return Response
     */
    protected function storeAlbum(
        ?string $languageIsoCode,
        int $mainMediaId,
        int $templateId,
        string $titleHtml,
        ?string $contentHtml,
        Request $request
    ): Response {
        $mainMedia = $this->mediaService->getMediaForId($mainMediaId);
        if (!$mainMedia) {
            return new BackofficeApiControllerStoreAlbumSuccessResponse(
                success: false,
                errorMessage: 'Main media ID not found',
            );
        }

        if (!Template::tryFrom($templateId)) {
            return new BackofficeApiControllerStoreAlbumSuccessResponse(
                success: false,
                errorMessage: 'Template ID not valid',
            );
        }

        $contentHtml = StringUtil::sanitiseHtml($contentHtml);
        $titleHtml = StringUtil::sanitiseHtml($titleHtml);

        $language = $languageIsoCode && Language::tryFrom(strtoupper($languageIsoCode))
            ? Language::from(strtoupper($languageIsoCode))
            : $request->siteLanguage;

        $newAlbum = $this->albumService->workflowStoreAlbum(
            language: $language,
            template: Template::from($templateId),
            user: $request->session->user,
            mainMedia: $mainMedia,
            titleHtml: $titleHtml,
            contentHtml: $contentHtml,
        );

        return new BackofficeApiControllerStoreAlbumSuccessResponse(
            success: (bool)$newAlbum,
            redirect: UrlBuilderUtil::buildBackofficeAlbumViewUrl(
                language: $request->siteLanguage,
                albumId: $newAlbum->id,
            ),
        );
    }

    /**
     * Endpoint: /back/album/{albumId}
     * Method: PUT
     *
     * @param int $albumId
     * @param string|null $languageIsoCode
     * @param int $mainMediaId
     * @param int $templateId
     * @param string $titleHtml
     * @param string|null $contentHtml
     * @param Request $request
     * @return Response
     */
    protected function updateAlbum(
        int $albumId,
        ?string $languageIsoCode,
        int $mainMediaId,
        int $templateId,
        string $titleHtml,
        ?string $contentHtml,
        Request $request
    ): Response {
        $album = $this->albumService->getAlbumForId($albumId);
        if (!$album) {
            return new BackofficeApiControllerUpdateAlbumSuccessResponse(
                success: false,
                errorMessage: 'Album ID not found',
            );
        }

        $mainMedia = $this->mediaService->getMediaForId($mainMediaId);
        if (!$mainMedia) {
            return new BackofficeApiControllerUpdateAlbumSuccessResponse(
                success: false,
                errorMessage: 'Main media ID not found',
            );
        }

        if (!Template::tryFrom($templateId)) {
            return new BackofficeApiControllerUpdateAlbumSuccessResponse(
                success: false,
                errorMessage: 'Template ID not valid',
            );
        }

        $contentHtml = StringUtil::sanitiseHtml($contentHtml);
        $titleHtml = StringUtil::sanitiseHtml($titleHtml);

        $language = $languageIsoCode && Language::tryFrom(strtoupper($languageIsoCode))
            ? Language::from(strtoupper($languageIsoCode))
            : $request->siteLanguage;

        $updatedAlbum = $this->albumService->workflowUpdateAlbum(
            existingAlbum: $album,
            language: $language,
            template: Template::from($templateId),
            mainMedia: $mainMedia,
            titleHtml: $titleHtml,
            contentHtml: $contentHtml,
        );

        return new BackofficeApiControllerUpdateAlbumSuccessResponse(
            success: (bool)$updatedAlbum,
            redirect: UrlBuilderUtil::buildBackofficeAlbumViewUrl(
                language: $request->siteLanguage,
                albumId: $albumId,
            ),
        );
    }

    /**
     * Endpoint: /back/album/{albumId}/status/{statusId}
     * Method: PUT
     *
     * @param int $albumId
     * @param int $statusId
     * @param Request $request
     * @return Response
     */
    protected function updateAlbumStatus(
        int $albumId,
        int $statusId,
        Request $request
    ): Response {
        $album = $this->albumService->getAlbumForId($albumId);
        if (!$album) {
            return new BackofficeApiControllerUpdateAlbumSuccessResponse(
                success: false,
                errorMessage: 'Album ID not found',
            );
        }

        if (!AlbumStatus::tryFrom($statusId)) {
            return new BackofficeApiControllerUpdateAlbumSuccessResponse(
                success: false,
                errorMessage: 'Status ID not valid',
            );
        }

        $newStatus = AlbumStatus::from($statusId);
        if ($album->status === $newStatus) {
            return new BackofficeApiControllerUpdateAlbumSuccessResponse(
                success: true,
            );
        }

        $res = $this->albumService->workflowUpdateAlbumStatus(
            albumId: $albumId,
            newStatus: $newStatus,
        );

        $link = UrlBuilderUtil::buildPublicAlbumUrl(
            slug: $album->slug->slug,
            language: $album->language,
        );

        return new BackofficeApiControllerUpdateAlbumStatusSuccessResponse(
            success: $res,
            publicLinkHtml: $newStatus->isPublished()
                ? ('<a href="' . $link . '">' . $link . '</a>')
                : $link,
        );
    }

    /**
     * Endpoint: /back/album/{albumId}/section
     * Method: POST
     *
     * @param int $albumId
     * @param int|null $mainMediaId
     * @param string|null $titleHtml
     * @param string|null $subtitleHtml
     * @param string|null $contentHtml
     * @param Request $request
     * @return Response
     */
    protected function storeAlbumSection(
        int $albumId,
        ?int $mainMediaId,
        ?string $titleHtml,
        ?string $subtitleHtml,
        ?string $contentHtml,
        Request $request
    ): Response {
        $album = $this->albumService->getAlbumForId($albumId);
        if (!$album) {
            return new BackofficeApiControllerUpdateAlbumSuccessResponse(
                success: false,
                errorMessage: 'Album ID not found',
            );
        }

        $mainMedia = $mainMediaId
            ? $this->mediaService->getMediaForId($mainMediaId)
            : null;

        if ($mainMediaId && !$mainMedia) {
            return new BackofficeApiControllerUpdateAlbumSuccessResponse(
                success: false,
                errorMessage: 'Main media ID not found',
            );
        }

        $contentHtml = StringUtil::sanitiseHtml($contentHtml);
        $titleHtml = StringUtil::sanitiseHtml($titleHtml);

        $newAlbumSection = $this->albumService->workflowStoreAlbumSection(
            album: $album,
            mainMedia: $mainMedia,
            titleHtml: $titleHtml,
            subtitleHtml: $subtitleHtml,
            contentHtml: $contentHtml,
        );

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return new BackofficeApiControllerStoreAlbumSectionSuccessResponse(
            success: true,
            newSectionId: $newAlbumSection->id,
            html: AlbumHtmlGenerator::generateAlbumSectionHtml(
                section: $newAlbumSection,
                localisationUtil: $localisationUtil,
            ),
        );
    }

    /**
     * Endpoint: /back/album-section/{albumSectionId}/media
     * Method: POST
     *
     * @param int $albumSectionId
     * @param int $mediaId
     * @param string|null $captionHtml
     * @param Request $request
     * @return Response
     */
    protected function storeMediaForAlbumSection(
        int $albumSectionId,
        int $mediaId,
        ?string $captionHtml,
        Request $request
    ): Response {
        $albumSection = $this->albumService->getAlbumSectionForId($albumSectionId);
        if (!$albumSection) {
            return new BackofficeApiControllerStoreMediaForAlbumSectionSuccessResponse(
                success: false,
                errorMessage: 'Album section ID not found',
            );
        }

        $media = $this->mediaService->getMediaForId($mediaId);
        if (!$media) {
            return new BackofficeApiControllerStoreMediaForAlbumSectionSuccessResponse(
                success: false,
                errorMessage: 'Media ID not found',
            );
        }

        $existingMedia = $this->albumService->getAlbumSectionMediaForIds(
            albumSectionId: $albumSectionId,
            mediaId: $mediaId,
        );

        if ($existingMedia) {
            return new BackofficeApiControllerStoreMediaForAlbumSectionSuccessResponse(
                success: true,
            );
        }

        $captionHtml = StringUtil::sanitiseHtml($captionHtml);

        $newAlbumMedia = $this->albumService->workflowStoreMediaForAlbumSection(
            albumSection: $albumSection,
            media: $media,
            captionHtml: $captionHtml,
        );

        return new BackofficeApiControllerStoreMediaForAlbumSectionSuccessResponse(
            success: (bool)$newAlbumMedia,
            html: AlbumHtmlGenerator::generateAlbumSectionMediaHtml(
                albumSectionMedia: $newAlbumMedia,
            ),
        );
    }

    /**
     * Endpoint: /back/album-section/{albumSectionId}/sequence
     * Method: PUT
     *
     * @param int $albumSectionId
     * @param int $albumSectionMediaIdTo
     * @param int $albumSectionMediaIdFrom
     * @param Request $request
     * @return Response
     */
    protected function updateMediaSequenceForAlbumSection(
        int $albumSectionId,
        int $albumSectionMediaIdTo,
        int $albumSectionMediaIdFrom,
        Request $request
    ): Response {
        $albumSection = $this->albumService->getAlbumSectionForId($albumSectionId);
        if (!$albumSection) {
            return new BackofficeApiControllerUpdateMediaSequenceForAlbumSectionSuccessResponse(
                success: false,
                errorMessage: 'Album section ID not found',
            );
        }

        $albumSectionMedias = $this->albumService->filterAlbumSectionMediaBy(
            albumSectionMediaIds: [$albumSectionMediaIdTo, $albumSectionMediaIdFrom],
        );

        if (count($albumSectionMedias) !== 2) {
            return new BackofficeApiControllerUpdateMediaSequenceForAlbumSectionSuccessResponse(
                success: false,
                errorMessage: 'Album section media IDs mismatch',
            );
        }

        $albumSectionMediaTo = $albumSectionMedias[0]->id === $albumSectionMediaIdTo
            ? $albumSectionMedias[0]
            : $albumSectionMedias[1];

        $albumSectionMediaFrom = $albumSectionMedias[0]->id === $albumSectionMediaIdFrom
            ? $albumSectionMedias[0]
            : $albumSectionMedias[1];

        $res = $this->albumService->updateMediaSequenceForAlbumSection(
            albumSectionMediaFrom: $albumSectionMediaFrom,
            albumSectionMediaTo: $albumSectionMediaTo,
        );

        return new BackofficeApiControllerUpdateMediaSequenceForAlbumSectionSuccessResponse(
            success: $res,
        );
    }

    /**
     * Endpoint: /back/album-section/{albumSectionId}
     * Method: PUT
     *
     * @param int $albumSectionId
     * @param int|null $mainMediaId
     * @param string|null $titleHtml
     * @param string|null $subtitleHtml
     * @param string|null $contentHtml
     * @param int|null $albumSectionIdSequenceTo
     * @param Request $request
     * @return Response
     */
    protected function updateAlbumSection(
        int $albumSectionId,
        ?int $mainMediaId,
        ?string $titleHtml,
        ?string $subtitleHtml,
        ?string $contentHtml,
        ?int $albumSectionIdSequenceTo,
        Request $request
    ): Response {
        $existingAlbumSection = $this->albumService->getAlbumSectionForId($albumSectionId);
        if (!$existingAlbumSection) {
            return new BackofficeApiControllerUpdateAlbumSectionSuccessResponse(
                success: false,
                errorMessage: 'Album section ID not found',
            );
        }

        $existingAlbumSectionTo = $albumSectionIdSequenceTo
            ? $this->albumService->getAlbumSectionForId($albumSectionIdSequenceTo)
            : null;

        if ($albumSectionIdSequenceTo && !$existingAlbumSectionTo) {
            return new BackofficeApiControllerUpdateAlbumSectionSuccessResponse(
                success: false,
                errorMessage: 'Album section ID not found',
            );
        }

        $media = $mainMediaId
            ? $this->mediaService->getMediaForId($mainMediaId)
            : null;

        if ($mainMediaId && !$media) {
            return new BackofficeApiControllerUpdateAlbumSectionSuccessResponse(
                success: false,
                errorMessage: 'Media ID not found',
            );
        }

        $contentHtml = StringUtil::sanitiseHtml($contentHtml);
        $titleHtml = StringUtil::sanitiseHtml($titleHtml);

        $res = $this->albumService->workflowUpdateAlbumSection(
            albumSectionFrom: $existingAlbumSection,
            albumSectionTo: $existingAlbumSectionTo,
            updated: new AlbumSection(
                id: $existingAlbumSection->id,
                albumId: $existingAlbumSection->albumId,
                mainMedia: $media,
                titleHtml: $titleHtml,
                subtitleHtml: $subtitleHtml,
                contentHtml: $contentHtml,
                createdAt: $existingAlbumSection->createdAt,
                updatedAt: new DateTimeImmutable(),
                sequence: $existingAlbumSectionTo
                    ? $existingAlbumSectionTo->sequence
                    : $existingAlbumSection->sequence,
            ),
        );

        return new BackofficeApiControllerUpdateAlbumSectionSuccessResponse(
            success: $res,
        );
    }

    /**
     * Endpoint: /back/album-section-media/{albumSectionMediaId}
     * Method: PUT
     *
     * @param int $albumSectionMediaId
     * @param string|null $captionHtml
     * @param int|null $sequence
     * @param Request $request
     * @return Response
     */
    protected function updateAlbumSectionMedia(
        int $albumSectionMediaId,
        ?string $captionHtml,
        ?int $sequence,
        Request $request
    ): Response {
        $existingAlbumSectionMedia = $this->albumService->getAlbumSectionMediaForId($albumSectionMediaId);
        if (!$existingAlbumSectionMedia) {
            return new BackofficeApiControllerUpdateAlbumSectionMediaSuccessResponse(
                success: false,
                errorMessage: 'Album section media ID not found',
            );
        }

        $captionHtml = StringUtil::sanitiseHtml($captionHtml);

        $this->albumService->updateAlbumSectionMedia(
            new AlbumSectionMedia(
                id: $existingAlbumSectionMedia->id,
                albumSectionId: $existingAlbumSectionMedia->albumSectionId,
                media: $existingAlbumSectionMedia->media,
                captionHtml: $captionHtml,
                createdAt: $existingAlbumSectionMedia->createdAt,
                updatedAt: new DateTimeImmutable(),
                sequence: $sequence ?? $existingAlbumSectionMedia->sequence,
            ),
        );

        return new BackofficeApiControllerUpdateAlbumSectionMediaSuccessResponse(
            success: true,
        );
    }

    /**
     * Endpoint: /back/album-section-media/{albumSectionMediaId}
     * Method: DELETE
     *
     * @param int $albumSectionMediaId
     * @param Request $request
     * @return Response
     */
    protected function deleteAlbumSectionMedia(
        int $albumSectionMediaId,
        Request $request
    ): Response {
        $existingAlbumSectionMedia = $this->albumService->getAlbumSectionMediaForId($albumSectionMediaId);
        if (!$existingAlbumSectionMedia) {
            return new BackofficeApiControllerDeleteAlbumSectionMediaSuccessResponse(
                success: false,
                errorMessage: 'Not found',
            );
        }

        $res = $this->albumService->workflowDeleteMediaForAlbumSection($existingAlbumSectionMedia);

        return new BackofficeApiControllerDeleteAlbumSectionMediaSuccessResponse(
            success: $res,
        );
    }
}
