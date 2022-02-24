<?php

namespace Amora\Core\Router;

use Amora\Core\Model\Util\QueryOptions;
use Amora\Core\Model\Util\QueryOrderBy;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Module\User\Value\VerificationType;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\Language;
use Amora\Core\Value\QueryOrderDirection;
use DateTimeImmutable;
use Throwable;
use Amora\Core\Core;
use Amora\Core\Util\Logger;
use Amora\Core\Model\Response;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\Tag;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Service\TagService;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Model\Request;
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
use Amora\Core\Router\Controller\Response\BackofficeApiControllerUpdateArticleFailureResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerUpdateArticleSuccessResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerUpdateUserFailureResponse;
use Amora\Core\Router\Controller\Response\BackofficeApiControllerUpdateUserSuccessResponse;

final class BackofficeApiController extends BackofficeApiControllerAbstract
{
    public function __construct(
        private Logger $logger,
        private UserService $userService,
        private ArticleService $articleService,
        private TagService $tagService,
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
     * @param int|null $languageId
     * @param int|null $roleId
     * @param string|null $timezone
     * @param bool|null $isEnabled
     * @param string|null $newPassword
     * @param string|null $repeatPassword
     * @param Request $request
     * @return Response
     */
    protected function storeUser(
        string $name,
        string $email,
        ?string $bio,
        ?int $languageId,
        ?int $roleId,
        ?string $timezone,
        ?bool $isEnabled,
        ?string $newPassword,
        ?string $repeatPassword,
        Request $request
    ): Response {
        $now = new DateTimeImmutable();
        $email = StringUtil::normaliseEmail($email);
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguageIsoCode);

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
                    UrlBuilderUtil::buildPublicLoginUrl($request->siteLanguageIsoCode)
                ),
            );
        }

        $languageId = $languageId ?? Language::getIdForIsoCode($request->siteLanguageIsoCode);
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
                    languageId: $languageId,
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
            redirect: UrlBuilderUtil::buildBackofficeUsersUrl($request->siteLanguageIsoCode),
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
     * @param int|null $languageId
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
        ?int $languageId,
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
            $existingUser,
            $name,
            $email,
            $languageId,
            $timezone,
            $currentPassword,
            $newPassword,
            $repeatPassword,
            StringUtil::isTrue($isEnabled)
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
            redirect: UrlBuilderUtil::buildBackofficeUsersUrl($request->siteLanguageIsoCode),
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
                : Core::getLocalisationUtil($request->siteLanguageIsoCode)->getValue('globalGenericError')
        );
    }

    /**
     * Endpoint: /back/article
     * Method: POST
     *
     * @param string|null $languageIsoCode
     * @param int $statusId
     * @param int|null $typeId
     * @param string|null $title
     * @param string $contentHtml
     * @param string|null $uri
     * @param int|null $mainImageId
     * @param string|null $publishOn
     * @param array $sections
     * @param array|null $tags
     * @param Request $request
     * @return Response
     */
    public function storeArticle(
        ?string $languageIsoCode,
        int $statusId,
        ?int $typeId,
        ?string $title,
        string $contentHtml,
        ?string $uri,
        ?int $mainImageId,
        ?string $publishOn,
        array $sections,
        ?array $tags,
        Request $request
    ): Response {
        if ($typeId && !ArticleType::tryFrom($typeId)) {
            return new BackofficeApiControllerStoreArticleSuccessResponse(
                success: false,
                errorMessage: 'Invalid article type',
            );
        }

        if ($statusId && !ArticleStatus::tryFrom($statusId) === null) {
            return new BackofficeApiControllerStoreArticleSuccessResponse(
                success: false,
                errorMessage: 'Invalid article status',
            );
        }

        $now = new DateTimeImmutable();
        $uri = $this->articleService->getAvailableUriForArticle(articleTitle: $title);
        $status = ArticleStatus::from($statusId);
        $publishOnObj = $publishOn
            ? DateUtil::convertStringToDateTimeImmutable($publishOn)
            : ($status === ArticleStatus::Published ? $now : null);

        $newArticle = $this->articleService->createNewArticle(
            article: new Article(
                id: null,
                user: $request->session->user,
                status: $status,
                type: $typeId ? ArticleType::from($typeId) : ArticleType::Page,
                createdAt: $now,
                updatedAt: $now,
                publishOn: $publishOnObj,
                title: $title,
                contentHtml: html_entity_decode($contentHtml),
                mainImageId: $mainImageId,
                mainImage: null,
                uri: $uri,
                tags: [],
            ),
            sections: $sections ?? [],
            tags: $tags ?? [],
            userIp: $request->sourceIp,
            userAgent: $request->userAgent,
        );

        $languageIsoCode = $languageIsoCode ?? Core::getDefaultLanguageIsoCode();
        $backofficeUri = $typeId === ArticleType::Blog
            ? UrlBuilderUtil::buildBackofficeBlogPostUrl($languageIsoCode, $newArticle->id)
            : UrlBuilderUtil::buildBackofficeArticleUrl($languageIsoCode, $newArticle->id);

        return new BackofficeApiControllerStoreArticleSuccessResponse(
            success: (bool)$newArticle,
            articleId: $newArticle?->id,
            articleBackofficeUri: $backofficeUri,
            articlePublicUri: $newArticle ? '/' . $newArticle->uri : null,
        );
    }

    /**
     * Endpoint: /back/article/{articleId}
     * Method: PUT
     *
     * @param int $articleId
     * @param string|null $languageIsoCode
     * @param int $statusId
     * @param int|null $typeId
     * @param string|null $title
     * @param string $contentHtml
     * @param string|null $uri
     * @param int|null $mainImageId
     * @param string|null $publishOn
     * @param array $sections
     * @param array|null $tags
     * @param Request $request
     * @return Response
     */
    public function updateArticle(
        int $articleId,
        ?string $languageIsoCode,
        int $statusId,
        ?int $typeId,
        ?string $title,
        string $contentHtml,
        ?string $uri,
        ?int $mainImageId,
        ?string $publishOn,
        array $sections,
        ?array $tags,
        Request $request
    ): Response {
        if ($typeId && !ArticleType::tryFrom($typeId)) {
            return new BackofficeApiControllerUpdateArticleSuccessResponse(
                success: false,
                errorMessage: 'Invalid article type',
            );
        }

        if ($statusId && !ArticleStatus::tryFrom($statusId) === null) {
            return new BackofficeApiControllerUpdateArticleSuccessResponse(
                success: false,
                errorMessage: 'Invalid article status',
            );
        }

        $existingArticle = $this->articleService->getArticleForId($articleId);
        if (empty($existingArticle)) {
            return new BackofficeApiControllerUpdateArticleFailureResponse();
        }

        $uri = $this->articleService->getAvailableUriForArticle($uri, $title, $existingArticle);

        $contentHtml = html_entity_decode($contentHtml);
        $now = new DateTimeImmutable();

        $publishOnMySql = $publishOn
            ? DateUtil::convertStringToDateTimeImmutable($publishOn)
            : ($existingArticle->publishOn ?? $now);

        $type = $typeId ? ArticleType::from($typeId) : $existingArticle->type;
        $status = $statusId ? ArticleStatus::from($statusId) : $existingArticle->status;
        $res = $this->articleService->workflowUpdateArticle(
            article: new Article(
                id: $articleId,
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
                uri: $uri
            ),
            sections: $sections,
            tags: $tags ?? [],
            userIp: $request->sourceIp,
            userAgent: $request->userAgent,
        );

        $languageIsoCode = $languageIsoCode ?? Core::getDefaultLanguageIsoCode();
        $backofficeUri = $type === ArticleType::Blog
            ? UrlBuilderUtil::buildBackofficeBlogPostUrl($languageIsoCode, $articleId)
            : UrlBuilderUtil::buildBackofficeArticleUrl($languageIsoCode, $articleId);

        return new BackofficeApiControllerUpdateArticleSuccessResponse(
            success: $res,
            articleId: $res ? $articleId : null,
            articleBackofficeUri: $backofficeUri,
            articlePublicUri: $res ? '/' . $uri : null,
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
                uri: $existingArticle->uri,
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
        $tags = $this->tagService->getAllTags(true);
        return new BackofficeApiControllerGetTagsSuccessResponse(
            success: true,
            tags: $tags,
        );
    }
}
