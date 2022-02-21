<?php

namespace Amora\Core\Router;

use Throwable;
use Amora\Core\Logger;
use Amora\Core\Module\User\Service\UserMailService;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Module\Article\Model\Image;
use Amora\Core\Module\Article\Service\ImageService;
use Amora\Core\Model\Request;
use Amora\Core\Model\Response;
use Amora\Core\Router\Controller\Response\{AuthorisedApiControllerDestroyImageFailureResponse,
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
        private ImageService $imageService,
        private UserService $userService,
        private UserMailService $userMailService,
    ) {
        parent::__construct();
    }

    public function authenticate(Request $request): bool
    {
        $session = $request->session;

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
            $session = $request->session;
            $images = $this->imageService->processImages(
                files: $request->processedFiles,
                userId: $session->user->id,
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
                    'id' => $img->id,
                    'url' => $img->fullUrlLarge,
                    'caption' => $img->caption,
                ];
            }

            return new AuthorisedApiControllerStoreImageSuccessResponse(
                !empty($output),
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

        $session = $request->session;
        if (!$session->isAdmin()
            && $image->userId != $session->user->id
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

        $resVerification = $this->userMailService->sendVerificationEmail(
            $user,
            $user->getEmail()
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

        $session = $request->session;
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
            $updateRes->isSuccess(),
            $updateRes->getMessage()
        );
    }
}
