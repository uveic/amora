<?php

namespace Amora\Core\Router;

use Amora\Core\Model\Util\QueryOptions;
use Amora\Core\Model\Util\QueryOrderBy;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\User\Service\UserMailService;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Module\Article\Service\MediaService;
use Amora\Core\Model\Request;
use Amora\Core\Model\Response;
use Amora\Core\Value\QueryOrderDirection;
use Amora\Core\Router\Controller\Response\{AuthorisedApiControllerDestroyFileSuccessResponse,
    AuthorisedApiControllerDestroyFileUnauthorisedResponse,
    AuthorisedApiControllerGetFileSuccessResponse,
    AuthorisedApiControllerGetNextFileSuccessResponse,
    AuthorisedApiControllerSendVerificationEmailFailureResponse,
    AuthorisedApiControllerSendVerificationEmailSuccessResponse,
    AuthorisedApiControllerStoreFileSuccessResponse,
    AuthorisedApiControllerUpdateUserAccountFailureResponse,
    AuthorisedApiControllerUpdateUserAccountUnauthorisedResponse,
    AuthorisedApiControllerUpdateUserAccountSuccessResponse};

final class AuthorisedApiController extends AuthorisedApiControllerAbstract
{
    public function __construct(
        private readonly MediaService $mediaService,
        private readonly UserService $userService,
        private readonly UserMailService $userMailService,
        private readonly ArticleService $articleService,
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
     * @param Request $request
     * @return Response
     */
    protected function storeFile(Request $request): Response
    {
        if (!$request->files) {
            return new AuthorisedApiControllerStoreFileSuccessResponse(
                success: false,
                file: [],
                errorMessage: 'No files selected',
            );
        }

        $res = $this->mediaService->workflowStoreFile(
            rawFiles: $request->files,
            user: $request->session->user,
        );

        if (!$res->isSuccess) {
            return new AuthorisedApiControllerStoreFileSuccessResponse(
                success: false,
                file: [],
                errorMessage: $res->message,
            );
        }

        /** @var Media $newFile */
        $newFile = $res->response;
        return new AuthorisedApiControllerStoreFileSuccessResponse(
            success: true,
            file: $newFile->buildPublicDataArray(),
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
        $file = $this->mediaService->getMediaForId($id);
        if (empty($file)) {
            return new AuthorisedApiControllerGetFileSuccessResponse(
                success: false,
                errorMessage: 'File not found',
            );
        }

        $statusIds = $request->session->isAdmin()
            ? [ArticleStatus::Published->value, ArticleStatus::Private->value]
            : [ArticleStatus::Published->value];

        $articles = $this->articleService->filterArticlesBy(
            statusIds: $statusIds,
            imageIds: [$file->id],
        );

        $appearsOn = [];
        /** @var Article $article */
        foreach ($articles as $article) {
            $appearsOn[] = $article->buildPublicDataArray();
        }

        $output = $file->buildPublicDataArray();
        $output['appearsOn'] = $appearsOn;

        return new AuthorisedApiControllerGetFileSuccessResponse(
            success: true,
            file: $output,
            tags: [],
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
        $file = $this->mediaService->getMediaForId($id);
        if (empty($file)) {
            return new AuthorisedApiControllerDestroyFileSuccessResponse(
                success: false,
                errorMessage: 'File not found',
            );
        }

        $session = $request->session;
        if (!$session->isAdmin() && $file->user?->id != $session->user->id) {
            return new AuthorisedApiControllerDestroyFileUnauthorisedResponse();
        }

        $res = $this->mediaService->deleteFile($file);
        return new AuthorisedApiControllerDestroyFileSuccessResponse($res);
    }

    /**
     * Endpoint: /api/file/{id}/next
     * Method: GET
     *
     * @param int $id
     * @param string|null $direction
     * @param int|null $qty
     * @param int|null $typeId
     * @param \Amora\Core\Model\Request $request
     * @return Response
     */
    protected function getNextFile(
        int $id,
        ?string $direction,
        ?int $qty,
        ?int $typeId,
        Request $request
    ): Response {
        $direction = isset($direction) ? strtoupper(trim($direction)) : QueryOrderDirection::DESC->value;
        $direction = QueryOrderDirection::tryFrom($direction)
            ? QueryOrderDirection::from($direction)
            : QueryOrderDirection::DESC;

        $qty = $qty ?? 10;
        $typeIds = isset($typeId) && MediaType::tryFrom($typeId)
            ? [MediaType::from($typeId)->value]
            : [];

        $files = $this->mediaService->filterMediaBy(
            typeIds: $typeIds,
            statusIds: [MediaStatus::Active->value],
            fromId: $id,
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy('id', $direction)],
                pagination: new Response\Pagination(itemsPerPage: $qty),
            ),
        );

        $output = [];
        /** @var Media $file */
        foreach ($files as $file) {
            $fileOutput = $file->buildPublicDataArray();
            $statusIds = $request->session->isAdmin()
                ? [ArticleStatus::Published->value, ArticleStatus::Private->value]
                : [ArticleStatus::Published->value];

            $articles = $this->articleService->filterArticlesBy(
                statusIds: $statusIds,
                imageIds: [$file->id],
            );

            $appearsOn = [];
            /** @var Article $article */
            foreach ($articles as $article) {
                $appearsOn[] = $article->buildPublicDataArray();
            }

            $fileOutput['appearsOn'] = $appearsOn;
            $output[] = $fileOutput;
        }

        return new AuthorisedApiControllerGetNextFileSuccessResponse(
            success: true,
            files: $output,
        );
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
