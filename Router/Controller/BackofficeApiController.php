<?php

namespace Amora\Router;

use Throwable;
use Amora\Core\Core;
use Amora\Core\Logger;
use Amora\Core\Model\Response;
use Amora\Core\Module\Action\Service\ActionService;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\Tag;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Service\ImageService;
use Amora\Core\Module\Article\Service\TagService;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Model\Request;
use Amora\Core\Module\User\Service\SessionService;
use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;
use Amora\Router\Controller\Response\BackofficeApiControllerCheckArticleUriSuccessResponse;
use Amora\Router\Controller\Response\BackofficeApiControllerDestroyArticleFailureResponse;
use Amora\Router\Controller\Response\BackofficeApiControllerDestroyArticleSuccessResponse;
use Amora\Router\Controller\Response\BackofficeApiControllerDestroyUserFailureResponse;
use Amora\Router\Controller\Response\BackofficeApiControllerDestroyUserSuccessResponse;
use Amora\Router\Controller\Response\BackofficeApiControllerGetTagsSuccessResponse;
use Amora\Router\Controller\Response\BackofficeApiControllerStoreArticleSuccessResponse;
use Amora\Router\Controller\Response\BackofficeApiControllerStoreTagFailureResponse;
use Amora\Router\Controller\Response\BackofficeApiControllerStoreTagSuccessResponse;
use Amora\Router\Controller\Response\BackofficeApiControllerStoreUserSuccessResponse;
use Amora\Router\Controller\Response\BackofficeApiControllerUpdateArticleFailureResponse;
use Amora\Router\Controller\Response\BackofficeApiControllerUpdateArticleSuccessResponse;
use Amora\Router\Controller\Response\BackofficeApiControllerUpdateUserFailureResponse;
use Amora\Router\Controller\Response\BackofficeApiControllerUpdateUserSuccessResponse;

final class BackofficeApiController extends BackofficeApiControllerAbstract
{
    public function __construct(
        private Logger $logger,
        private ActionService $actionService,
        private SessionService $sessionService,
        private UserService $userService,
        private ArticleService $articleService,
        private ImageService $imageService,
        private TagService $tagService
    ) {
        parent::__construct();
    }

    public function authenticate(Request $request): bool
    {
        $session = $request->getSession();
        $this->actionService->logAction($request, $session);
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
     * @param int $languageId
     * @param int $roleId
     * @param string $timezone
     * @param bool $isEnabled
     * @param string|null $newPassword
     * @param string|null $repeatPassword
     * @param Request $request
     * @return Response
     */
    protected function storeUser(
        string $name,
        string $email,
        ?string $bio,
        int $languageId,
        int $roleId,
        string $timezone,
        bool $isEnabled,
        ?string $newPassword,
        ?string $repeatPassword,
        Request $request
    ): Response {
        $now = DateUtil::getCurrentDateForMySql();
        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());

        if ($newPassword !== $repeatPassword) {
            return new BackofficeApiControllerStoreUserSuccessResponse(
                false,
                $localisationUtil->getValue('authenticationPasswordsDoNotMatch')
            );
        }

        // ToDo: Replace with an email to create first password
        if (empty($newPassword)) {
            $newPassword = 'testuve123';
        }

        $email = StringUtil::normaliseEmail($email);

        if (!StringUtil::isEmailAddressValid($email)) {
            return new BackofficeApiControllerStoreUserSuccessResponse(
                false,
                $localisationUtil->getValue('authenticationEmailNotValid')
            );
        }

        if (strlen($newPassword) < UserService::USER_PASSWORD_MIN_LENGTH) {
            return new BackofficeApiControllerStoreUserSuccessResponse(
                false,
                $localisationUtil->getValue('authenticationPasswordTooShort')
            );
        }

        $existingUser =$this->userService->getUserForEmail($email);
        if (!empty($existingUser)) {
            return new BackofficeApiControllerStoreUserSuccessResponse(
                false,
                $localisationUtil->getValue('authenticationRegistrationErrorExistingEmail')
            );
        }

        try {
            $this->userService->storeUser(
                new User(
                    null,
                    $languageId,
                    $roleId,
                    UserJourneyStatus::getInitialJourneyIdFromRoleId($roleId),
                    $now,
                    $now,
                    $email,
                    $name,
                    StringUtil::hashPassword($newPassword),
                    $bio,
                    $isEnabled,
                    true,
                    $timezone
                )
            );
        } catch (Throwable $t) {
            $this->logger->logError('Error creating new user: ' . $t->getMessage());
            return new BackofficeApiControllerStoreUserSuccessResponse(
                false,
                $localisationUtil->getValue('globalGenericError')
            );
        }

        return new BackofficeApiControllerStoreUserSuccessResponse(true);
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

        if ($updateRes->isError()) {
            return new BackofficeApiControllerUpdateUserSuccessResponse(
                false,
                $updateRes->getMessage()
            );
        }

        return new BackofficeApiControllerUpdateUserSuccessResponse(true);
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
     * Endpoint: /back/article/uri
     * Method: POST
     *
     * @param int|null $articleId
     * @param string $uri
     * @param Request $request
     * @return Response
     */
    protected function checkArticleUri(?int $articleId, string $uri, Request $request): Response
    {
        $res = $this->articleService->checkUriAndReturnAnAvailableOne($uri, $articleId);
        return new BackofficeApiControllerCheckArticleUriSuccessResponse(true, $res);
    }

    /**
     * Endpoint: /back/article
     * Method: POST
     *
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

        if (empty($uri)) {
            $uri = $this->articleService->checkUriAndReturnAnAvailableOne(
                StringUtil::getRandomString(32)
            );
        }
        $uri = StringUtil::cleanString(trim($uri, '/ '));

        if (empty($publishOn)) {
            $publishOn = $statusId === ArticleStatus::PUBLISHED ? $now : null;
        }

        $res = $this->articleService->createNewArticle(
            new Article(
                null,
                $request->getSession()->getUser(),
                $statusId,
                $typeId ?? ArticleType::ARTICLE,
                $now,
                $now,
                $publishOn,
                $title,
                html_entity_decode($contentHtml),
                $mainImageId,
                null,
                $uri,
            ),
            $sections ?? [],
            $tags ?? [],
            $request->getSourceIp(),
            $request->getUserAgent()
        );

        return new BackofficeApiControllerStoreArticleSuccessResponse(
            $res ? true : false,
            $res ? $res->getId() : null,
            $res ? '/' . $res->getUri() : null
        );
    }

    /**
     * Endpoint: /back/article/{articleId}
     * Method: PUT
     *
     * @param int $articleId
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

        if (empty($uri)) {
            $uri = $this->articleService->checkUriAndReturnAnAvailableOne(
                StringUtil::getRandomString(32)
            );
        }
        $uri = StringUtil::cleanString(trim($uri, '/ '));

        $contentHtml = html_entity_decode($contentHtml);
        $now = DateUtil::getCurrentDateForMySql();

        $res = $this->articleService->updateArticle(
            new Article(
                $articleId,
                $request->getSession()->getUser(),
                $statusId ?? $existingArticle->getStatusId(),
                $typeId ?? $existingArticle->getTypeId(),
                $existingArticle->getCreatedAt(),
                $now,
                $publishOn ?? $existingArticle->getPublishOn(),
                $title ?? $existingArticle->getTitle(),
                $contentHtml ?? $existingArticle->getContentHtml(),
                $mainImageId ?? $existingArticle->getMainImage()?->getId(),
                null,
                $uri
            ),
            $sections,
            $tags ?? [],
            $request->getSourceIp(),
            $request->getUserAgent()
        );

        return new BackofficeApiControllerUpdateArticleSuccessResponse(
            $res,
            $res ? $articleId : null,
            $res ? '/' . $uri : null
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
                $existingArticle->getId(),
                $existingArticle->getUser(),
                ArticleStatus::DELETED,
                $existingArticle->getTypeId(),
                $existingArticle->getCreatedAt(),
                DateUtil::getCurrentDateForMySql(),
                $existingArticle->getPublishOn(),
                $existingArticle->getTitle(),
                $existingArticle->getContentHtml(),
                $existingArticle->getMainImage() ? $existingArticle->getMainImage()->getId() : null,
                $existingArticle->getMainImage(),
                $existingArticle->getUri()
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
