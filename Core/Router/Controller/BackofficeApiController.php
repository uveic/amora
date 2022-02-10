<?php

namespace Amora\Core\Router;

use Amora\Core\Model\Util\QueryOptions;
use Amora\Core\Model\Util\QueryOrderBy;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Module\User\Value\VerificationType;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\Language;
use Throwable;
use Amora\Core\Core;
use Amora\Core\Logger;
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
        $session = $request->getSession();
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
        $now = DateUtil::getCurrentDateForMySql();
        $email = StringUtil::normaliseEmail($email);
        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());

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
                    UrlBuilderUtil::buildPublicLoginUrl($request->getSiteLanguage())
                ),
            );
        }

        $languageId = $languageId ?? Language::getIdForIsoCode($request->getSiteLanguage());
        $roleId = $roleId ?? UserRole::USER;
        $timezone = $timezone ?? $request->getSession()->getUser()->getTimezone();
        $isEnabled = $isEnabled ?? true;

        try {
            $newUser = $this->userService->storeUser(
                user: new User(
                    id: null,
                    languageId: $languageId,
                    roleId: $roleId,
                    journeyStatusId: UserJourneyStatus::getInitialJourneyIdFromRoleId($roleId),
                    createdAt: $now,
                    updatedAt: $now,
                    email: $email,
                    name: $name,
                    passwordHash: null,
                    bio: $bio,
                    isEnabled: $isEnabled,
                    verified: false,
                    timezone: $timezone
                ),
                verificationEmailId: VerificationType::PASSWORD_CREATION,
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
            id: $newUser?->getId(),
            redirect: UrlBuilderUtil::buildBackofficeUsersUrl($request->getSiteLanguage()),
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
                orderBy: [new QueryOrderBy('name', 'ASC')],
                pagination: new Response\Pagination(itemsPerPage: 25),
            ),
        );

        $output = [];
        /** @var User $user */
        foreach ($users as $user) {
            $output[] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
            ];
        }

        return new BackofficeApiControllerGetUsersSuccessResponse(true, $output);
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

        if (!$updateRes->isSuccess()) {
            return new BackofficeApiControllerUpdateUserSuccessResponse(
                false,
                null,
                $updateRes->getMessage()
            );
        }

        return new BackofficeApiControllerUpdateUserSuccessResponse(
            true,
            UrlBuilderUtil::buildBackofficeUsersUrl($request->getSiteLanguage())
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
            $res,
            $res
                ? null
                : Core::getLocalisationUtil(
                    $request->getSiteLanguage()
                )->getValue('globalGenericError')
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
        $now = DateUtil::getCurrentDateForMySql();
        $uri = $this->articleService->getAvailableUriForArticle(articleTitle: $title);

        $publishOnMySql = $publishOn
            ? DateUtil::convertDateFromISOToMySQLFormat($publishOn)
            : ($statusId === ArticleStatus::PUBLISHED->value ? $now : null);

        $res = $this->articleService->createNewArticle(
            new Article(
                id: null,
                user: $request->getSession()->getUser(),
                statusId: $statusId,
                typeId: $typeId ?? ArticleType::PAGE,
                createdAt: $now,
                updatedAt: $now,
                publishOn: $publishOnMySql,
                title: $title,
                contentHtml: html_entity_decode($contentHtml),
                mainImageId: $mainImageId,
                mainImage: null,
                uri: $uri,
                tags: [],
            ),
            $sections ?? [],
            $tags ?? [],
            $request->getSourceIp(),
            $request->getUserAgent()
        );

        $languageIsoCode = $languageIsoCode ?? Core::getDefaultLanguageIsoCode();
        $backofficeUri = $typeId === ArticleType::BLOG
            ? UrlBuilderUtil::buildBackofficeBlogPostUrl($languageIsoCode, $res->getId())
            : UrlBuilderUtil::buildBackofficeArticleUrl($languageIsoCode, $res->getId());

        return new BackofficeApiControllerStoreArticleSuccessResponse(
            success: (bool)$res,
            articleId: $res?->getId(),
            articleBackofficeUri: $backofficeUri,
            articlePublicUri: $res ? '/' . $res->getUri() : null,
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
        $existingArticle = $this->articleService->getArticleForId($articleId);
        if (empty($existingArticle)) {
            return new BackofficeApiControllerUpdateArticleFailureResponse();
        }

        $uri = $this->articleService->getAvailableUriForArticle($uri, $title, $existingArticle);

        $contentHtml = html_entity_decode($contentHtml);
        $now = DateUtil::getCurrentDateForMySql();

        $publishOnMySql = $publishOn
            ? DateUtil::convertDateFromISOToMySQLFormat($publishOn)
            : ($existingArticle->getPublishOn() ?? $now);

        $typeId = $typeId ?? $existingArticle->getTypeId();
        $res = $this->articleService->workflowUpdateArticle(
            new Article(
                id: $articleId,
                user: $request->getSession()->getUser(),
                statusId: $statusId ?? $existingArticle->getStatusId(),
                typeId: $typeId,
                createdAt: $existingArticle->getCreatedAt(),
                updatedAt: $now,
                publishOn: $publishOnMySql,
                title: $title ?? $existingArticle->getTitle(),
                contentHtml: $contentHtml ?? $existingArticle->getContentHtml(),
                mainImageId: $mainImageId ?? $existingArticle->getMainImage()?->getId(),
                mainImage: null,
                uri: $uri
            ),
            $sections,
            $tags ?? [],
            $request->getSourceIp(),
            $request->getUserAgent()
        );

        $languageIsoCode = $languageIsoCode ?? Core::getDefaultLanguageIsoCode();
        $backofficeUri = $typeId === ArticleType::BLOG
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
            new Article(
                id: $existingArticle->getId(),
                user: $existingArticle->getUser(),
                statusId: ArticleStatus::DELETED->value,
                typeId: $existingArticle->getTypeId(),
                createdAt: $existingArticle->getCreatedAt(),
                updatedAt: DateUtil::getCurrentDateForMySql(),
                publishOn: $existingArticle->getPublishOn(),
                title: $existingArticle->getTitle(),
                contentHtml: $existingArticle->getContentHtml(),
                mainImageId: $existingArticle->getMainImage()?->getId(),
                mainImage: $existingArticle->getMainImage(),
                uri: $existingArticle->getUri(),
            ),
            $request->getSourceIp(),
            $request->getUserAgent()
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
            return new BackofficeApiControllerStoreTagSuccessResponse(true, $existingTag->getId());
        }

        $res = $this->tagService->storeTag(new Tag(null, $name));

        if (empty($res)) {
            return new BackofficeApiControllerStoreTagFailureResponse();
        }

        return new BackofficeApiControllerStoreTagSuccessResponse(true, $res->getId());
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
        return new BackofficeApiControllerGetTagsSuccessResponse(true, $tags);
    }
}
