<?php

namespace Amora\Core\Router;

use Amora\Core\Module\Article\Model\Image;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\User\Service\UserMailService;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Module\Article\Service\ImageService;
use Amora\Core\Model\Request;
use Amora\Core\Model\Response;
use Amora\Core\Router\Controller\Response\{AuthorisedApiControllerDestroyFileSuccessResponse,
    AuthorisedApiControllerDestroyImageFailureResponse,
    AuthorisedApiControllerDestroyImageUnauthorisedResponse,
    AuthorisedApiControllerDestroyImageSuccessResponse,
    AuthorisedApiControllerGetFileSuccessResponse,
    AuthorisedApiControllerSendVerificationEmailFailureResponse,
    AuthorisedApiControllerSendVerificationEmailSuccessResponse,
    AuthorisedApiControllerStoreFileSuccessResponse,
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
     * Endpoint: /api/file
     * Method: POST
     *
     * @param int $fileTypeId
     * @param Request $request
     * @return Response
     */
    protected function storeFile(int $fileTypeId, Request $request): Response
    {
        if (!MediaType::tryFrom($fileTypeId)) {
            return new AuthorisedApiControllerStoreFileSuccessResponse(
                success: false,
                file: [],
                errorMessage: 'File type not valid: ' . $fileTypeId,
            );
        }

        if (!$request->processedFiles) {
            return new AuthorisedApiControllerStoreFileSuccessResponse(
                success: false,
                file: [],
                errorMessage: 'No files selected',
            );
        }

        $newFile = $this->formService->storeFormFile(
            rawFile: $request->processedFiles[0],
            fileType: MediaType::from($fileTypeId),
            status: MediaStatus::Active,
            userId: $request->session?->user->id,
        );

        if (empty($newFile) || empty($newFile->id)) {
            return new AuthorisedApiControllerStoreFileSuccessResponse(
                success: false,
                file: [],
                errorMessage: 'File not valid: max file size exceeded.'
            );
        }

        return new AuthorisedApiControllerStoreFileSuccessResponse(
            success: true,
            file: [
                'id' => $formFile->id,
                'path' => $formFile->filePath,
                'caption' => DocumentType::getCaption($documentType),
                'name' => $formFile->fileName,
            ],
        );
    }

    /**
     * Endpoint: /api/file/{id}
     * Method: GET
     *
     * @param int $id
     * @param Request $request
     * @return Response
     */
    protected function getFile(int $id, Request $request): Response
    {
        return new AuthorisedApiControllerGetFileSuccessResponse(
            success: true,
            file: [
                'id' => 1,
                'name' => 'filename.jpg',
                'caption' => 'This is the caption',
                'path' => 'http://localhost:8888/uploads/20220817194145kW3cGXuEz6y5n1JQ.jpg',
            ],
        );
    }

    /**
     * Endpoint: /api/file/{id}
     * Method: DELETE
     *
     * @param int $id
     * @param Request $request
     * @return Response
     */
    protected function destroyFile(int $id, Request $request): Response
    {
        return new AuthorisedApiControllerDestroyFileSuccessResponse(
            success: true,
        );
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
