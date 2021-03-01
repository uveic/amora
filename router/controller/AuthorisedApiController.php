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
use uve\router\controller\response\{AuthorisedApiControllerDestroyImageFailureResponse,
    AuthorisedApiControllerDestroyImageUnauthorisedResponse,
    AuthorisedApiControllerDestroyImageSuccessResponse,
    AuthorisedApiControllerSendVerificationEmailFailureResponse,
    AuthorisedApiControllerSendVerificationEmailSuccessResponse,
    AuthorisedApiControllerStoreImageSuccessResponse,
    AuthorisedApiControllerUpdateUserAccountFailureResponse,
    AuthorisedApiControllerUpdateUserAccountUnauthorisedResponse,
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
     * @param Request $request
     * @return Response
     */
    public function storeImage(Request $request): Response
    {
        try {
            $session = $request->getSession();
            $images = $this->imageService->processImages(
                $request->getFiles(),
                $session->getUser()->getId()
            );

            $imgSaved = [];
            foreach ($images as $image) {
                $res = $this->imageService->storeImage($image);
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

            return new AuthorisedApiControllerStoreImageSuccessResponse(false, []);
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
            return new AuthorisedApiControllerDestroyImageFailureResponse();
        }

        $session = $request->getSession();
        if (!$session->isAdmin()
            && $image->getUserId() != $session->getUser()->getId()
        ) {
            return new AuthorisedApiControllerDestroyImageUnauthorisedResponse();
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
            return new AuthorisedApiControllerSendVerificationEmailFailureResponse();
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
            return new AuthorisedApiControllerUpdateUserAccountFailureResponse();
        }

        $session = $request->getSession();
        if ($session->getUser()->getId() !== $existingUser->getId()) {
            return new AuthorisedApiControllerUpdateUserAccountUnauthorisedResponse();
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
