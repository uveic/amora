<?php

namespace Amora\Core\Router;

use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Article\Model\ArticlePath;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Module\User\Value\VerificationType;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerGetPreviousPathsForArticleSuccessResponse;
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
                errorMessage: sprintf(
                    $localisationUtil->getValue('authenticationRegistrationErrorExistingEmail'),
                    UrlBuilderUtil::buildPublicLoginUrl($request->siteLanguage)
                ),
            );
        }

        $userRole = $roleId
            ? UserRole::from($roleId)
            : UserRole::User;
        $timezone = $timezone
            ? DateUtil::convertStringToDateTimeZone($timezone)
            : $request->session->user->timezone;
        $isEnabled = $isEnabled ?? true;

        try {
            $newUser = $this->userService->storeUser(
                user: new User(
                    id: null,
                    language: $language,
                    role: $userRole,
                    journeyStatus: UserJourneyStatus::getInitialUserJourneyStatusFromRole($userRole),
                    createdAt: $now,
                    updatedAt: $now,
                    email: $email,
                    name: $name,
                    passwordHash: null,
                    bio: $bio,
                    isEnabled: $isEnabled,
                    verified: false,
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
            redirect: UrlBuilderUtil::buildBackofficeUsersUrl($request->siteLanguage),
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

        $users = $this->userService->filterUsersBy(
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
     * @param bool $isEnabled
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
        ?bool $isEnabled,
        ?string $currentPassword,
        ?string $newPassword,
        ?string $repeatPassword,
        Request $request
    ): Response {
        $existingUser = $this->userService->getUserForId($userId, true);
        if (empty($existingUser)) {
            return new BackofficeApiControllerUpdateUserFailureResponse();
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
            isEnabled: StringUtil::isTrue($isEnabled),
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
            redirect: UrlBuilderUtil::buildBackofficeUsersUrl($request->siteLanguage),
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
     * @param string|null $path
     * @param int|null $mainImageId
     * @param string|null $publishOn
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
        ?string $path,
        ?int $mainImageId,
        ?string $publishOn,
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
        $contentHtml = html_entity_decode($contentHtml);
        $contentHtml = StringUtil::sanitiseHtml($contentHtml);
        $path = StringUtil::sanitiseText($path);

        $now = new DateTimeImmutable();
        $articleLanguage = Language::from(strtoupper($articleLanguageIsoCode));
        $path = $path ?: $this->articleService->getAvailablePathForArticle(articleTitle: $title);
        $status = ArticleStatus::from($statusId);
        $publishOn = $publishOn
            ? DateUtil::convertStringToDateTimeImmutable($publishOn)
            : ($status === ArticleStatus::Published ? $now : null);

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
            sections: $sections ?? [],
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
        return new BackofficeApiControllerStoreArticleSuccessResponse(
            success: (bool)$newArticle,
            articleId: $newArticle->id,
            articleBackofficePath: UrlBuilderUtil::buildBackofficeArticleUrl($siteLanguage, $newArticle->id),
            articlePublicPath: UrlBuilderUtil::buildPublicArticlePath($newArticle->path, $siteLanguage),
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
     * @param string|null $path
     * @param int|null $mainImageId
     * @param string|null $publishOn
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
        ?string $path,
        ?int $mainImageId,
        ?string $publishOn,
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
        if (empty($existingArticle)) {
            return new BackofficeApiControllerUpdateArticleSuccessResponse(
                success: false,
                errorMessage: 'Article not found',
            );
        }

        $title = StringUtil::sanitiseText($title);
        $contentHtml = html_entity_decode($contentHtml);
        $contentHtml = StringUtil::sanitiseHtml($contentHtml);
        $path = StringUtil::sanitiseText($path);

        $path = $this->articleService->getAvailablePathForArticle($path, $title, $existingArticle);
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
        $publishOnMySql = $publishOn
            ? DateUtil::convertStringToDateTimeImmutable($publishOn)
            : ($existingArticle->publishOn ?? $now);

        $articleLanguage = Language::from(strtoupper($articleLanguageIsoCode));
        $type = $typeId ? ArticleType::from($typeId) : $existingArticle->type;
        $status = $statusId ? ArticleStatus::from($statusId) : $existingArticle->status;
        $res = $this->articleService->workflowUpdateArticle(
            article: new Article(
                id: $articleId,
                language: $articleLanguage,
                user: $request->session->user,
                status: $status,
                type: $type,
                createdAt: $existingArticle->createdAt,
                updatedAt: $now,
                publishOn: $publishOnMySql,
                title: $title ?? $existingArticle->title,
                contentHtml: $contentHtml ?? $existingArticle->contentHtml,
                mainImageId: $mainImageId ?? $existingArticle->mainImage?->id,
                mainImage: null,
                path: $path
            ),
            sections: $sections,
            tags: $tags ?? [],
            userIp: $request->sourceIp,
            userAgent: $request->userAgent,
        );

        return new BackofficeApiControllerUpdateArticleSuccessResponse(
            success: $res,
            articleId: $res ? $articleId : null,
            articleBackofficePath: UrlBuilderUtil::buildBackofficeArticleUrl(
                language: Language::from(strtoupper($siteLanguageIsoCode)),
                articleId: $articleId,
            ),
            articlePublicPath: $res ? '/' . $path : null,
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
     * Endpoint: /back/article/{articleId}/previous-path
     * Method: GET
     *
     * @param int $articleId
     * @param Request $request
     * @return Response
     */
    protected function getPreviousPathsForArticle(
        int $articleId,
        Request $request
    ): Response {
        $paths = $this->articleService->filterArticlePathsBy(
            articleIds: [$articleId],
            queryOptions: new QueryOptions(
                orderBy: [
                    new QueryOrderBy('created_at', QueryOrderDirection::DESC),
                ],
            ),
        );

        $output = [];
        /** @var ArticlePath $path */
        foreach ($paths as $path) {
            $output[] = $path->asPublicArray();
        }

        return new BackofficeApiControllerGetPreviousPathsForArticleSuccessResponse(
            success: true,
            paths: $output,
        );
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
        $name = StringUtil::sanitiseText($name);

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
}
