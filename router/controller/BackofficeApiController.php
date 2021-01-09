<?php

namespace uve\router;

use Throwable;
use uve\core\Logger;
use uve\core\model\Response;
use uve\core\module\action\service\ActionService;
use uve\core\module\article\model\Article;
use uve\core\module\article\service\ArticleService;
use uve\core\module\article\service\ImageService;
use uve\core\module\article\value\ArticleStatus;
use uve\core\module\user\model\User;
use uve\core\module\user\service\UserService;
use uve\core\model\Request;
use uve\core\module\user\service\SessionService;
use uve\core\module\user\value\UserJourneyStatus;
use uve\core\util\DateUtil;
use uve\core\util\StringUtil;
use uve\router\controller\response\BackofficeApiControllerDeleteUserSuccessResponse;
use uve\router\controller\response\BackofficeApiControllerDestroyArticleSuccessResponse;
use uve\router\controller\response\BackofficeApiControllerStoreArticleSuccessResponse;
use uve\router\controller\response\BackofficeApiControllerStoreUserSuccessResponse;
use uve\router\controller\response\BackofficeApiControllerUpdateArticleSuccessResponse;
use uve\router\controller\response\BackofficeApiControllerUpdateUserFailureResponse;
use uve\router\controller\response\BackofficeApiControllerUpdateUserSuccessResponse;

final class BackofficeApiController extends BackofficeApiControllerAbstract
{
    public function __construct(
        private Logger $logger,
        private ActionService $actionService,
        private SessionService $sessionService,
        private UserService $userService,
        private ArticleService $articleService,
        private ImageService $imageService,
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

        if ($newPassword !== $repeatPassword) {
            return new BackofficeApiControllerStoreUserSuccessResponse(
                false,
                'Passwords do not match'
            );
        }

        if (empty($newPassword)) {
            $newPassword = 'testuve123';
        }

        $email = StringUtil::normaliseEmail($email);

        if (!StringUtil::isEmailAddressValid($email)) {
            return new BackofficeApiControllerStoreUserSuccessResponse(
                false,
                'Correo electrónico non válido'
            );
        }

        if (strlen($newPassword) < UserService::USER_PASSWORD_MIN_LENGTH) {
            return new BackofficeApiControllerStoreUserSuccessResponse(
                false,
                'A lonxitude mínima do contrasinal son ' .
                UserService::USER_PASSWORD_MIN_LENGTH .
                ' caracteres. Corríxeo e volve a intentalo.'
            );
        }

        $existingUser =$this->userService->getUserForEmail($email);
        if (!empty($existingUser)) {
            return new BackofficeApiControllerStoreUserSuccessResponse(
                false,
                'Xa hai outra conta co mesmo email.'
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
                'Error creating user'
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
            return new BackofficeApiControllerUpdateUserFailureResponse('User not found');
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
            return new BackofficeApiControllerUpdateUserFailureResponse($updateRes->getMessage());
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
    protected function deleteUser(int $userId, Request $request): Response
    {
        $user = $this->userService->getUserForId($userId, true);
        if (empty($user)) {
            return new BackofficeApiControllerDeleteUserSuccessResponse(false, 'Not found');
        }

        $res = $this->userService->deleteUser($user);

        return new BackofficeApiControllerDeleteUserSuccessResponse(
            $res,
            $res ? null : 'Something went wrong, please try again.'
        );
    }

    /**
     * Endpoint: /back/article
     * Method: POST
     *
     * @param int $statusId
     * @param int $typeId
     * @param string $title
     * @param string $content
     * @param string $uri
     * @param string|null $mainImageSrc
     * @param Request $request
     * @return Response
     */
    public function storeArticle(
        int $statusId,
        int $typeId,
        string $title,
        string $content,
        string $uri,
        ?string $mainImageSrc,
        Request $request
    ): Response {
        $now = DateUtil::getCurrentDateForMySql();

        $this->articleService->createNewArticle(
            new Article(
                null,
                $request->getSession()->getUser(),
                $statusId,
                $typeId,
                $now,
                $now,
                $title,
                html_entity_decode($content),
                $mainImageSrc,
                $uri,
                []
            ),
            $request->getSourceIp(),
            $request->getUserAgent()
        );

        return new BackofficeApiControllerStoreArticleSuccessResponse(true);
    }

    /**
     * Endpoint: /back/article/{articleId}
     * Method: PUT
     *
     * @param int $articleId
     * @param int|null $statusId
     * @param int|null $typeId
     * @param string|null $title
     * @param string|null $content
     * @param string|null $uri
     * @param string|null $mainImageSrc
     * @param Request $request
     * @return Response
     */
    public function updateArticle(
        int $articleId,
        ?int $statusId,
        ?int $typeId,
        ?string $title,
        ?string $content,
        ?string $uri,
        ?string $mainImageSrc,
        Request $request
    ): Response {
        $existingArticle = $this->articleService->getArticleForId($articleId);
        if (empty($existingArticle)) {
            return new BackofficeApiControllerDestroyArticleSuccessResponse(
                false,
                'Article not found'
            );
        }

        if ($content) {
            $content = html_entity_decode($content);
        }

        $this->articleService->updateArticle(
            new Article(
                $articleId,
                $request->getSession()->getUser(),
                $statusId ?? $existingArticle->getStatusId(),
                $typeId ?? $existingArticle->getTypeId(),
                $existingArticle->getCreatedAt(),
                DateUtil::getCurrentDateForMySql(),
                $title ?? $existingArticle->getTitle(),
                $content ?? $existingArticle->getContent(),
                $mainImageSrc ?? $existingArticle->getMainImageSrc(),
                $uri,
                $existingArticle->getImages()
            ),
            $request->getSourceIp(),
            $request->getUserAgent()
        );

        return new BackofficeApiControllerUpdateArticleSuccessResponse(true);
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
            return new BackofficeApiControllerDestroyArticleSuccessResponse(
                false,
                'Article not found'
            );
        }

        $deleteRes = $this->articleService->deleteArticle(
            new Article(
                $existingArticle->getId(),
                $existingArticle->getUser(),
                ArticleStatus::DELETED,
                $existingArticle->getTypeId(),
                $existingArticle->getCreatedAt(),
                DateUtil::getCurrentDateForMySql(),
                $existingArticle->getTitle(),
                $existingArticle->getContent(),
                $existingArticle->getMainImageSrc(),
                $existingArticle->getUri(),
                $existingArticle->getImages()
            ),
            $request->getSourceIp(),
            $request->getUserAgent()
        );

        return new BackofficeApiControllerDestroyArticleSuccessResponse($deleteRes);
    }
}
