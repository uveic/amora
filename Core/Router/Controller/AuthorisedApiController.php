<?php

namespace Amora\Core\Router;

use Amora\Core\Module\Article\Model\Image;
use Amora\Core\Module\User\Service\UserMailService;
use Amora\Core\Module\User\Service\UserService;
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
        if (!$request->processedFiles) {
            return new AuthorisedApiControllerStoreImageSuccessResponse(
                success: false,
                images: [],
            );
        }

        $images = $this->imageService->processAndStoreRawImages(
            rawImages: $request->processedFiles,
            userId: $request->session->user->id,
        );

        $output = [];
        /** @var Image $image */
        foreach ($images as $image) {
            $output[] = $image->buildPublicDataArray();
        }

        return new AuthorisedApiControllerStoreImageSuccessResponse(
            success: (bool)$output,
            images: $output,
        );
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
            user: $user,
            emailToVerify: $user->email,
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
     * @param string|null $languageIsoCode
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
        ?string $languageIsoCode,
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
        if ($session->user->id !== $existingUser->id) {
            return new AuthorisedApiControllerUpdateUserAccountUnauthorisedResponse();
        }

        $updateRes = $this->userService->workflowUpdateUser(
            existingUser: $existingUser,
            name: $name,
            email: $email,
            languageIsoCode: $languageIsoCode,
            timezone: $timezone,
            currentPassword: $currentPassword,
            newPassword: $newPassword,
            repeatPassword: $repeatPassword,
        );

        return new AuthorisedApiControllerUpdateUserAccountSuccessResponse(
            success: $updateRes->isSuccess,
            errorMessage: $updateRes->message,
        );
    }
}
