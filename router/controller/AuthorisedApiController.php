<?php

namespace uve\router;

use Throwable;
use uve\core\Logger;
use uve\core\module\action\service\ActionService;
use uve\core\module\user\service\UserMailService;
use uve\core\module\user\service\UserService;
use uve\core\module\user\value\VerificationType;
use uve\core\module\article\model\Image;
use uve\core\module\article\service\ArticleService;
use uve\core\module\article\service\ImageService;
use uve\core\model\Request;
use uve\core\model\Response;
use uve\core\module\user\service\SessionService;
use uve\router\controller\response\{
    AuthorisedApiControllerDestroyImageSuccessResponse,
    AuthorisedApiControllerSendVerificationEmailSuccessResponse,
    AuthorisedApiControllerStoreImageForbiddenResponse,
    AuthorisedApiControllerStoreImageSuccessResponse,
    AuthorisedApiControllerUpdateUserAccountForbiddenResponse,
    AuthorisedApiControllerUpdateUserAccountSuccessResponse};

final class AuthorisedApiController extends AuthorisedApiControllerAbstract
{
    public function __construct(
        private Logger $logger,
        private SessionService $sessionService,
        private ImageService $imageService,
        private ArticleService $articleService,
        private UserService $userService,
        private UserMailService $userMailService,
        private ActionService $actionService,
    ) {
        parent::__construct();
    }

    public function authenticate(Request $request): bool
    {
        $session = $request->getSession();
        $this->actionService->logAction($request, $session);

        if (empty($session) || !$session->isAuthenticated()) {
            return false;
        }

        return true;
    }

    /**
     * Endpoint: /api/image
     * Method: POST
     *
     * @param ?int $articleId
     * @param Request $request
     * @return Response
     */
    public function storeImage(?int $articleId, Request $request): Response
    {
        try {
            $session = $request->getSession();
            if ($articleId) {
                $article = $this->articleService->getArticleForId($articleId);
                if (empty($article) ||
                    (
                        !$session->isAdmin()
                        && $session->getUser()->getId() !== $article->getUser()->getId()
                    )
                ) {
                    return new AuthorisedApiControllerStoreImageForbiddenResponse();
                }
            }

            $images = $this->imageService->processImages(
                $request->getFiles(),
                $session->getUser()->getId()
            );

            $imgSaved = [];
            foreach ($images as $image) {
                $res = $this->imageService->storeImage($image, $articleId);
                if ($res) {
                    $imgSaved[] = $res;
                }
            }

            $output = [];
            /** @var Image $img */
            foreach ($imgSaved as $img) {
                $output[] = [
                    'id' => $img->getId(),
                    'url' => $img->getFullUrlBig(),
                    'caption' => $img->getCaption()
                ];
            }

            return new AuthorisedApiControllerStoreImageSuccessResponse(
                empty($output) ? false : true,
                $output
            );
        } catch (Throwable $t) {
            $this->logger->logError(
                'AuthorisedApiController - Error storing image: ' .
                $t->getMessage() .
                ' - Trace: ' . $t->getTraceAsString()
            );

            return new AuthorisedApiControllerStoreImageSuccessResponse(
                false,
                [],
                'Unexpected error saving image'
            );
        }
    }

    /**
     * Endpoint: /api/image
     * Method: DELETE
     *
     * @param int $imageId
     * @param int|null $eventId
     * @param Request $request
     * @return Response
     */
    public function destroyImage(int $imageId, ?int $eventId, Request $request): Response
    {
        $image = $this->imageService->getImageForId($imageId);
        if (empty($image)) {
            return new AuthorisedApiControllerDestroyImageSuccessResponse(
                false,
                'Image not found'
            );
        }

        $session = $request->getSession();
        if (!$session->isAdmin()
            && $image->getUserId() != $session->getUser()->getId()
        ) {
            return new AuthorisedApiControllerDestroyImageSuccessResponse(
                false,
                'Not authorised to delete this image'
            );
        }

        $res = $this->imageService->deleteImage($image);
        return new AuthorisedApiControllerDestroyImageSuccessResponse($res);
    }

    /**
     * Endpoint: /api/user/{userId}/verification-email
     * Method: POST
     *
     * @param int $userId
     * @param Request $request
     * @return Response
     */
    protected function sendVerificationEmail(int $userId, Request $request): Response
    {
        $user = $this->userService->getUserForId($userId);
        if (empty($user)) {
            return new AuthorisedApiControllerSendVerificationEmailSuccessResponse(false);
        }

        $resVerification = $this->userMailService->buildAndSendVerificationEmail(
            $user,
            VerificationType::ACCOUNT
        );
        return new AuthorisedApiControllerSendVerificationEmailSuccessResponse($resVerification);
    }

    /**
     * Endpoint: /api/user/{userId}
     * Method: PUT
     *
     * @param int $userId
     * @param string|null $name
     * @param string|null $email
     * @param string|null $languageId
     * @param string|null $timezone
     * @param string|null $currentPassword
     * @param string|null $newPassword
     * @param string|null $repeatPassword
     * @param Request $request
     * @return Response
     */
    protected function updateUserAccount(
        int $userId,
        ?string $name,
        ?string $email,
        ?string $languageId,
        ?string $timezone,
        ?string $currentPassword,
        ?string $newPassword,
        ?string $repeatPassword,
        Request $request
    ): Response {
        $existingUser = $this->userService->getUserForId($userId);
        if (empty($existingUser)) {
            return new AuthorisedApiControllerUpdateUserAccountSuccessResponse(
                false,
                'User not found'
            );
        }

        $session = $request->getSession();
        if ($session->getUser()->getId() !== $existingUser->getId()) {
            return new AuthorisedApiControllerUpdateUserAccountForbiddenResponse();
        }

        $updateRes = $this->userService->workflowUpdateUser(
            $existingUser,
            $name,
            $email,
            $languageId,
            $timezone,
            $currentPassword,
            $newPassword,
            $repeatPassword
        );

        return new AuthorisedApiControllerUpdateUserAccountSuccessResponse(
            !$updateRes->isError(),
            $updateRes->getMessage()
        );
    }
}
