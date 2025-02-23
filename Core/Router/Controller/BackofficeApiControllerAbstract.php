<?php

namespace Amora\Core\Router;

use Amora\Core\Core;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Router\AbstractController;
use Amora\Core\Router\RouterCore;
use Amora\Core\Util\StringUtil;
use Throwable;

abstract class BackofficeApiControllerAbstract extends AbstractController
{
    public function __construct()
    {
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerGetSessionSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerStoreUserSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerStoreUserFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerStoreUserUnauthorisedResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerGetUsersSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerUpdateUserSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerUpdateUserFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerUpdateUserUnauthorisedResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerDestroyUserSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerDestroyUserFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerStoreArticleSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerStoreArticleFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerUpdateArticleSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerUpdateArticleFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerDestroyArticleSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerDestroyArticleFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerStoreTagSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerStoreTagFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerGetTagsSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerUpdatePageContentSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerUpdatePageContentFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerStoreAlbumSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerStoreAlbumFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerUpdateAlbumSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerUpdateAlbumFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerUpdateAlbumStatusSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerStoreAlbumSectionSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerStoreMediaForAlbumSectionSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerUpdateMediaSequenceForAlbumSectionSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerUpdateAlbumSectionSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerUpdateAlbumSectionMediaSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerDeleteAlbumSectionMediaSuccessResponse.php';
    }

    abstract protected function authenticate(Request $request): bool;

    /**
     * Endpoint: /back/session
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getSession(Request $request): Response;

    /**
     * Endpoint: /back/user
     * Method: POST
     *
     * @param string $name
     * @param string $email
     * @param string|null $bio
     * @param string|null $languageIsoCode
     * @param int|null $roleId
     * @param string|null $timezone
     * @param bool|null $isEnabled
     * @param Request $request
     * @return Response
     */
    abstract protected function storeUser(
        string $name,
        string $email,
        ?string $bio,
        ?string $languageIsoCode,
        ?int $roleId,
        ?string $timezone,
        ?bool $isEnabled,
        Request $request
    ): Response;

    /**
     * Endpoint: /back/user
     * Method: GET
     *
     * @param string|null $q
     * @param Request $request
     * @return Response
     */
    abstract protected function getUsers(?string $q, Request $request): Response;

    /**
     * Endpoint: /back/user/{userId}
     * Method: PUT
     *
     * @param int $userId
     * @param string|null $name
     * @param string|null $email
     * @param string|null $bio
     * @param string|null $languageIsoCode
     * @param int|null $roleId
     * @param string|null $timezone
     * @param int|null $userStatusId
     * @param string|null $currentPassword
     * @param string|null $newPassword
     * @param string|null $repeatPassword
     * @param Request $request
     * @return Response
     */
    abstract protected function updateUser(
        int $userId,
        ?string $name,
        ?string $email,
        ?string $bio,
        ?string $languageIsoCode,
        ?int $roleId,
        ?string $timezone,
        ?int $userStatusId,
        ?string $currentPassword,
        ?string $newPassword,
        ?string $repeatPassword,
        Request $request
    ): Response;

    /**
     * Endpoint: /back/user/{userId}
     * Method: DELETE
     *
     * @param int $userId
     * @param Request $request
     * @return Response
     */
    abstract protected function destroyUser(int $userId, Request $request): Response;

    /**
     * Endpoint: /back/article
     * Method: POST
     *
     * @param string $siteLanguageIsoCode
     * @param string $articleLanguageIsoCode
     * @param int $statusId
     * @param int $typeId
     * @param string|null $title
     * @param string $contentHtml
     * @param int|null $mainImageId
     * @param string|null $publishOn
     * @param ?array $mediaIds
     * @param array $sections
     * @param ?array $tags
     * @param Request $request
     * @return Response
     */
    abstract protected function storeArticle(
        string $siteLanguageIsoCode,
        string $articleLanguageIsoCode,
        int $statusId,
        int $typeId,
        ?string $title,
        string $contentHtml,
        ?int $mainImageId,
        ?string $publishOn,
        ?array $mediaIds,
        array $sections,
        ?array $tags,
        Request $request
    ): Response;

    /**
     * Endpoint: /back/article/{articleId}
     * Method: PUT
     *
     * @param int $articleId
     * @param string $siteLanguageIsoCode
     * @param string $articleLanguageIsoCode
     * @param int $statusId
     * @param int $typeId
     * @param string|null $title
     * @param string $contentHtml
     * @param int|null $mainImageId
     * @param string|null $publishOn
     * @param array $mediaIds
     * @param array $sections
     * @param ?array $tags
     * @param Request $request
     * @return Response
     */
    abstract protected function updateArticle(
        int $articleId,
        string $siteLanguageIsoCode,
        string $articleLanguageIsoCode,
        int $statusId,
        int $typeId,
        ?string $title,
        string $contentHtml,
        ?int $mainImageId,
        ?string $publishOn,
        array $mediaIds,
        array $sections,
        ?array $tags,
        Request $request
    ): Response;

    /**
     * Endpoint: /back/article/{articleId}
     * Method: DELETE
     *
     * @param int $articleId
     * @param Request $request
     * @return Response
     */
    abstract protected function destroyArticle(int $articleId, Request $request): Response;

    /**
     * Endpoint: /back/tag
     * Method: POST
     *
     * @param string $name
     * @param Request $request
     * @return Response
     */
    abstract protected function storeTag(string $name, Request $request): Response;

    /**
     * Endpoint: /back/tag
     * Method: GET
     *
     * @param string|null $name
     * @param Request $request
     * @return Response
     */
    abstract protected function getTags(?string $name, Request $request): Response;

    /**
     * Endpoint: /back/content/{contentId}
     * Method: PUT
     *
     * @param int $contentId
     * @param string|null $title
     * @param string|null $subtitle
     * @param string|null $contentHtml
     * @param int|null $mainImageId
     * @param string|null $actionUrl
     * @param Request $request
     * @return Response
     */
    abstract protected function updatePageContent(
        int $contentId,
        ?string $title,
        ?string $subtitle,
        ?string $contentHtml,
        ?int $mainImageId,
        ?string $actionUrl,
        Request $request
    ): Response;

    /**
     * Endpoint: /back/album
     * Method: POST
     *
     * @param string|null $languageIsoCode
     * @param int $mainMediaId
     * @param int $templateId
     * @param string $titleHtml
     * @param string|null $contentHtml
     * @param Request $request
     * @return Response
     */
    abstract protected function storeAlbum(
        ?string $languageIsoCode,
        int $mainMediaId,
        int $templateId,
        string $titleHtml,
        ?string $contentHtml,
        Request $request
    ): Response;

    /**
     * Endpoint: /back/album/{albumId}
     * Method: PUT
     *
     * @param int $albumId
     * @param string|null $languageIsoCode
     * @param int $mainMediaId
     * @param int $templateId
     * @param string $titleHtml
     * @param string|null $contentHtml
     * @param Request $request
     * @return Response
     */
    abstract protected function updateAlbum(
        int $albumId,
        ?string $languageIsoCode,
        int $mainMediaId,
        int $templateId,
        string $titleHtml,
        ?string $contentHtml,
        Request $request
    ): Response;

    /**
     * Endpoint: /back/album/{albumId}/status/{statusId}
     * Method: PUT
     *
     * @param int $albumId
     * @param int $statusId
     * @param Request $request
     * @return Response
     */
    abstract protected function updateAlbumStatus(
        int $albumId,
        int $statusId,
        Request $request
    ): Response;

    /**
     * Endpoint: /back/album/{albumId}/section
     * Method: POST
     *
     * @param int $albumId
     * @param int|null $mainMediaId
     * @param string|null $titleHtml
     * @param string|null $subtitleHtml
     * @param string|null $contentHtml
     * @param Request $request
     * @return Response
     */
    abstract protected function storeAlbumSection(
        int $albumId,
        ?int $mainMediaId,
        ?string $titleHtml,
        ?string $subtitleHtml,
        ?string $contentHtml,
        Request $request
    ): Response;

    /**
     * Endpoint: /back/album-section/{albumSectionId}/media
     * Method: POST
     *
     * @param int $albumSectionId
     * @param int $mediaId
     * @param string|null $captionHtml
     * @param Request $request
     * @return Response
     */
    abstract protected function storeMediaForAlbumSection(
        int $albumSectionId,
        int $mediaId,
        ?string $captionHtml,
        Request $request
    ): Response;

    /**
     * Endpoint: /back/album-section/{albumSectionId}/sequence
     * Method: PUT
     *
     * @param int $albumSectionId
     * @param int $albumSectionMediaIdTo
     * @param int $albumSectionMediaIdFrom
     * @param Request $request
     * @return Response
     */
    abstract protected function updateMediaSequenceForAlbumSection(
        int $albumSectionId,
        int $albumSectionMediaIdTo,
        int $albumSectionMediaIdFrom,
        Request $request
    ): Response;

    /**
     * Endpoint: /back/album-section/{albumSectionId}
     * Method: PUT
     *
     * @param int $albumSectionId
     * @param int|null $mainMediaId
     * @param string|null $titleHtml
     * @param string|null $subtitleHtml
     * @param string|null $contentHtml
     * @param int|null $albumSectionIdSequenceTo
     * @param Request $request
     * @return Response
     */
    abstract protected function updateAlbumSection(
        int $albumSectionId,
        ?int $mainMediaId,
        ?string $titleHtml,
        ?string $subtitleHtml,
        ?string $contentHtml,
        ?int $albumSectionIdSequenceTo,
        Request $request
    ): Response;

    /**
     * Endpoint: /back/album-section-media/{albumSectionMediaId}
     * Method: PUT
     *
     * @param int $albumSectionMediaId
     * @param string|null $captionHtml
     * @param int|null $sequence
     * @param Request $request
     * @return Response
     */
    abstract protected function updateAlbumSectionMedia(
        int $albumSectionMediaId,
        ?string $captionHtml,
        ?int $sequence,
        Request $request
    ): Response;

    /**
     * Endpoint: /back/album-section-media/{albumSectionMediaId}
     * Method: DELETE
     *
     * @param int $albumSectionMediaId
     * @param Request $request
     * @return Response
     */
    abstract protected function deleteAlbumSectionMedia(
        int $albumSectionMediaId,
        Request $request
    ): Response;

    private function validateAndCallGetSession(Request $request): Response
    {
        $errors = [];

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->getSession(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: getSession()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallStoreUser(Request $request): Response
    {
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $name = null;
        if (!isset($bodyParams['name'])) {
            $errors[] = [
                'field' => 'name',
                'message' => 'required'
            ];
        } else {
            $name = $bodyParams['name'] ?? null;
        }

        $email = null;
        if (!isset($bodyParams['email'])) {
            $errors[] = [
                'field' => 'email',
                'message' => 'required'
            ];
        } else {
            $email = $bodyParams['email'] ?? null;
        }

        $bio = $bodyParams['bio'] ?? null;
        $languageIsoCode = $bodyParams['languageIsoCode'] ?? null;
        $roleId = $bodyParams['roleId'] ?? null;
        $timezone = $bodyParams['timezone'] ?? null;
        $isEnabled = $bodyParams['isEnabled'] ?? null;

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->storeUser(
                $name,
                $email,
                $bio,
                $languageIsoCode,
                $roleId,
                $timezone,
                $isEnabled,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: storeUser()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetUsers(Request $request): Response
    {
        $queryParams = $request->getParams;
        $errors = [];


        $q = $queryParams['q'] ?? null;
        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->getUsers(
                $q,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: getUsers()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallUpdateUser(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['back', 'user', '{userId}'],
            $pathParts
        );
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        $userId = null;
        if (!isset($pathParams['userId'])) {
            $errors[] = [
                'field' => 'userId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['userId'])) {
                $errors[] = [
                    'field' => 'userId',
                    'message' => 'must be an integer'
                ];
            } else {
                $userId = intval($pathParams['userId']);
            }
        }

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $name = $bodyParams['name'] ?? null;
        $email = $bodyParams['email'] ?? null;
        $bio = $bodyParams['bio'] ?? null;
        $languageIsoCode = $bodyParams['languageIsoCode'] ?? null;
        $roleId = $bodyParams['roleId'] ?? null;
        $timezone = $bodyParams['timezone'] ?? null;
        $userStatusId = $bodyParams['userStatusId'] ?? null;
        $currentPassword = $bodyParams['currentPassword'] ?? null;
        $newPassword = $bodyParams['newPassword'] ?? null;
        $repeatPassword = $bodyParams['repeatPassword'] ?? null;

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->updateUser(
                $userId,
                $name,
                $email,
                $bio,
                $languageIsoCode,
                $roleId,
                $timezone,
                $userStatusId,
                $currentPassword,
                $newPassword,
                $repeatPassword,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: updateUser()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallDestroyUser(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['back', 'user', '{userId}'],
            $pathParts
        );
        $errors = [];

        $userId = null;
        if (!isset($pathParams['userId'])) {
            $errors[] = [
                'field' => 'userId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['userId'])) {
                $errors[] = [
                    'field' => 'userId',
                    'message' => 'must be an integer'
                ];
            } else {
                $userId = intval($pathParams['userId']);
            }
        }

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->destroyUser(
                $userId,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: destroyUser()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallStoreArticle(Request $request): Response
    {
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $siteLanguageIsoCode = null;
        if (!isset($bodyParams['siteLanguageIsoCode'])) {
            $errors[] = [
                'field' => 'siteLanguageIsoCode',
                'message' => 'required'
            ];
        } else {
            $siteLanguageIsoCode = $bodyParams['siteLanguageIsoCode'] ?? null;
        }

        $articleLanguageIsoCode = null;
        if (!isset($bodyParams['articleLanguageIsoCode'])) {
            $errors[] = [
                'field' => 'articleLanguageIsoCode',
                'message' => 'required'
            ];
        } else {
            $articleLanguageIsoCode = $bodyParams['articleLanguageIsoCode'] ?? null;
        }

        $statusId = null;
        if (!isset($bodyParams['statusId'])) {
            $errors[] = [
                'field' => 'statusId',
                'message' => 'required'
            ];
        } else {
            $statusId = $bodyParams['statusId'] ?? null;
        }

        $typeId = null;
        if (!isset($bodyParams['typeId'])) {
            $errors[] = [
                'field' => 'typeId',
                'message' => 'required'
            ];
        } else {
            $typeId = $bodyParams['typeId'] ?? null;
        }

        $title = $bodyParams['title'] ?? null;
        $contentHtml = null;
        if (!isset($bodyParams['contentHtml'])) {
            $errors[] = [
                'field' => 'contentHtml',
                'message' => 'required'
            ];
        } else {
            $contentHtml = $bodyParams['contentHtml'] ?? null;
        }

        $mainImageId = $bodyParams['mainImageId'] ?? null;
        $publishOn = $bodyParams['publishOn'] ?? null;
        $mediaIds = $bodyParams['mediaIds'] ?? null;
        $sections = null;
        if (!isset($bodyParams['sections'])) {
            $errors[] = [
                'field' => 'sections',
                'message' => 'required'
            ];
        } else {
            $sections = $bodyParams['sections'] ?? null;
        }

        $tags = $bodyParams['tags'] ?? null;

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->storeArticle(
                $siteLanguageIsoCode,
                $articleLanguageIsoCode,
                $statusId,
                $typeId,
                $title,
                $contentHtml,
                $mainImageId,
                $publishOn,
                $mediaIds,
                $sections,
                $tags,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: storeArticle()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallUpdateArticle(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['back', 'article', '{articleId}'],
            $pathParts
        );
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        $articleId = null;
        if (!isset($pathParams['articleId'])) {
            $errors[] = [
                'field' => 'articleId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['articleId'])) {
                $errors[] = [
                    'field' => 'articleId',
                    'message' => 'must be an integer'
                ];
            } else {
                $articleId = intval($pathParams['articleId']);
            }
        }

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $siteLanguageIsoCode = null;
        if (!isset($bodyParams['siteLanguageIsoCode'])) {
            $errors[] = [
                'field' => 'siteLanguageIsoCode',
                'message' => 'required'
            ];
        } else {
            $siteLanguageIsoCode = $bodyParams['siteLanguageIsoCode'] ?? null;
        }

        $articleLanguageIsoCode = null;
        if (!isset($bodyParams['articleLanguageIsoCode'])) {
            $errors[] = [
                'field' => 'articleLanguageIsoCode',
                'message' => 'required'
            ];
        } else {
            $articleLanguageIsoCode = $bodyParams['articleLanguageIsoCode'] ?? null;
        }

        $statusId = null;
        if (!isset($bodyParams['statusId'])) {
            $errors[] = [
                'field' => 'statusId',
                'message' => 'required'
            ];
        } else {
            $statusId = $bodyParams['statusId'] ?? null;
        }

        $typeId = null;
        if (!isset($bodyParams['typeId'])) {
            $errors[] = [
                'field' => 'typeId',
                'message' => 'required'
            ];
        } else {
            $typeId = $bodyParams['typeId'] ?? null;
        }

        $title = $bodyParams['title'] ?? null;
        $contentHtml = null;
        if (!isset($bodyParams['contentHtml'])) {
            $errors[] = [
                'field' => 'contentHtml',
                'message' => 'required'
            ];
        } else {
            $contentHtml = $bodyParams['contentHtml'] ?? null;
        }

        $mainImageId = $bodyParams['mainImageId'] ?? null;
        $publishOn = $bodyParams['publishOn'] ?? null;
        $mediaIds = null;
        if (!isset($bodyParams['mediaIds'])) {
            $errors[] = [
                'field' => 'mediaIds',
                'message' => 'required'
            ];
        } else {
            $mediaIds = $bodyParams['mediaIds'] ?? null;
        }

        $sections = null;
        if (!isset($bodyParams['sections'])) {
            $errors[] = [
                'field' => 'sections',
                'message' => 'required'
            ];
        } else {
            $sections = $bodyParams['sections'] ?? null;
        }

        $tags = $bodyParams['tags'] ?? null;

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->updateArticle(
                $articleId,
                $siteLanguageIsoCode,
                $articleLanguageIsoCode,
                $statusId,
                $typeId,
                $title,
                $contentHtml,
                $mainImageId,
                $publishOn,
                $mediaIds,
                $sections,
                $tags,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: updateArticle()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallDestroyArticle(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['back', 'article', '{articleId}'],
            $pathParts
        );
        $errors = [];

        $articleId = null;
        if (!isset($pathParams['articleId'])) {
            $errors[] = [
                'field' => 'articleId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['articleId'])) {
                $errors[] = [
                    'field' => 'articleId',
                    'message' => 'must be an integer'
                ];
            } else {
                $articleId = intval($pathParams['articleId']);
            }
        }

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->destroyArticle(
                $articleId,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: destroyArticle()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallStoreTag(Request $request): Response
    {
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $name = null;
        if (!isset($bodyParams['name'])) {
            $errors[] = [
                'field' => 'name',
                'message' => 'required'
            ];
        } else {
            $name = $bodyParams['name'] ?? null;
        }


        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->storeTag(
                $name,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: storeTag()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetTags(Request $request): Response
    {
        $queryParams = $request->getParams;
        $errors = [];


        $name = $queryParams['name'] ?? null;
        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->getTags(
                $name,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: getTags()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallUpdatePageContent(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['back', 'content', '{contentId}'],
            $pathParts
        );
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        $contentId = null;
        if (!isset($pathParams['contentId'])) {
            $errors[] = [
                'field' => 'contentId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['contentId'])) {
                $errors[] = [
                    'field' => 'contentId',
                    'message' => 'must be an integer'
                ];
            } else {
                $contentId = intval($pathParams['contentId']);
            }
        }

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $title = $bodyParams['title'] ?? null;
        $subtitle = $bodyParams['subtitle'] ?? null;
        $contentHtml = $bodyParams['contentHtml'] ?? null;
        $mainImageId = $bodyParams['mainImageId'] ?? null;
        $actionUrl = $bodyParams['actionUrl'] ?? null;

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->updatePageContent(
                $contentId,
                $title,
                $subtitle,
                $contentHtml,
                $mainImageId,
                $actionUrl,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: updatePageContent()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallStoreAlbum(Request $request): Response
    {
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $languageIsoCode = $bodyParams['languageIsoCode'] ?? null;
        $mainMediaId = null;
        if (!isset($bodyParams['mainMediaId'])) {
            $errors[] = [
                'field' => 'mainMediaId',
                'message' => 'required'
            ];
        } else {
            $mainMediaId = $bodyParams['mainMediaId'] ?? null;
        }

        $templateId = null;
        if (!isset($bodyParams['templateId'])) {
            $errors[] = [
                'field' => 'templateId',
                'message' => 'required'
            ];
        } else {
            $templateId = $bodyParams['templateId'] ?? null;
        }

        $titleHtml = null;
        if (!isset($bodyParams['titleHtml'])) {
            $errors[] = [
                'field' => 'titleHtml',
                'message' => 'required'
            ];
        } else {
            $titleHtml = $bodyParams['titleHtml'] ?? null;
        }

        $contentHtml = $bodyParams['contentHtml'] ?? null;

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->storeAlbum(
                $languageIsoCode,
                $mainMediaId,
                $templateId,
                $titleHtml,
                $contentHtml,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: storeAlbum()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallUpdateAlbum(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['back', 'album', '{albumId}'],
            $pathParts
        );
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        $albumId = null;
        if (!isset($pathParams['albumId'])) {
            $errors[] = [
                'field' => 'albumId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['albumId'])) {
                $errors[] = [
                    'field' => 'albumId',
                    'message' => 'must be an integer'
                ];
            } else {
                $albumId = intval($pathParams['albumId']);
            }
        }

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $languageIsoCode = $bodyParams['languageIsoCode'] ?? null;
        $mainMediaId = null;
        if (!isset($bodyParams['mainMediaId'])) {
            $errors[] = [
                'field' => 'mainMediaId',
                'message' => 'required'
            ];
        } else {
            $mainMediaId = $bodyParams['mainMediaId'] ?? null;
        }

        $templateId = null;
        if (!isset($bodyParams['templateId'])) {
            $errors[] = [
                'field' => 'templateId',
                'message' => 'required'
            ];
        } else {
            $templateId = $bodyParams['templateId'] ?? null;
        }

        $titleHtml = null;
        if (!isset($bodyParams['titleHtml'])) {
            $errors[] = [
                'field' => 'titleHtml',
                'message' => 'required'
            ];
        } else {
            $titleHtml = $bodyParams['titleHtml'] ?? null;
        }

        $contentHtml = $bodyParams['contentHtml'] ?? null;

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->updateAlbum(
                $albumId,
                $languageIsoCode,
                $mainMediaId,
                $templateId,
                $titleHtml,
                $contentHtml,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: updateAlbum()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallUpdateAlbumStatus(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['back', 'album', '{albumId}', 'status', '{statusId}'],
            $pathParts
        );
        $errors = [];

        $albumId = null;
        if (!isset($pathParams['albumId'])) {
            $errors[] = [
                'field' => 'albumId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['albumId'])) {
                $errors[] = [
                    'field' => 'albumId',
                    'message' => 'must be an integer'
                ];
            } else {
                $albumId = intval($pathParams['albumId']);
            }
        }

        $statusId = null;
        if (!isset($pathParams['statusId'])) {
            $errors[] = [
                'field' => 'statusId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['statusId'])) {
                $errors[] = [
                    'field' => 'statusId',
                    'message' => 'must be an integer'
                ];
            } else {
                $statusId = intval($pathParams['statusId']);
            }
        }

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->updateAlbumStatus(
                $albumId,
                $statusId,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: updateAlbumStatus()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallStoreAlbumSection(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['back', 'album', '{albumId}', 'section'],
            $pathParts
        );
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        $albumId = null;
        if (!isset($pathParams['albumId'])) {
            $errors[] = [
                'field' => 'albumId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['albumId'])) {
                $errors[] = [
                    'field' => 'albumId',
                    'message' => 'must be an integer'
                ];
            } else {
                $albumId = intval($pathParams['albumId']);
            }
        }

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $mainMediaId = $bodyParams['mainMediaId'] ?? null;
        $titleHtml = $bodyParams['titleHtml'] ?? null;
        $subtitleHtml = $bodyParams['subtitleHtml'] ?? null;
        $contentHtml = $bodyParams['contentHtml'] ?? null;

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->storeAlbumSection(
                $albumId,
                $mainMediaId,
                $titleHtml,
                $subtitleHtml,
                $contentHtml,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: storeAlbumSection()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallStoreMediaForAlbumSection(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['back', 'album-section', '{albumSectionId}', 'media'],
            $pathParts
        );
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        $albumSectionId = null;
        if (!isset($pathParams['albumSectionId'])) {
            $errors[] = [
                'field' => 'albumSectionId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['albumSectionId'])) {
                $errors[] = [
                    'field' => 'albumSectionId',
                    'message' => 'must be an integer'
                ];
            } else {
                $albumSectionId = intval($pathParams['albumSectionId']);
            }
        }

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $mediaId = null;
        if (!isset($bodyParams['mediaId'])) {
            $errors[] = [
                'field' => 'mediaId',
                'message' => 'required'
            ];
        } else {
            $mediaId = $bodyParams['mediaId'] ?? null;
        }

        $captionHtml = $bodyParams['captionHtml'] ?? null;

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->storeMediaForAlbumSection(
                $albumSectionId,
                $mediaId,
                $captionHtml,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: storeMediaForAlbumSection()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallUpdateMediaSequenceForAlbumSection(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['back', 'album-section', '{albumSectionId}', 'sequence'],
            $pathParts
        );
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        $albumSectionId = null;
        if (!isset($pathParams['albumSectionId'])) {
            $errors[] = [
                'field' => 'albumSectionId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['albumSectionId'])) {
                $errors[] = [
                    'field' => 'albumSectionId',
                    'message' => 'must be an integer'
                ];
            } else {
                $albumSectionId = intval($pathParams['albumSectionId']);
            }
        }

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $albumSectionMediaIdTo = null;
        if (!isset($bodyParams['albumSectionMediaIdTo'])) {
            $errors[] = [
                'field' => 'albumSectionMediaIdTo',
                'message' => 'required'
            ];
        } else {
            $albumSectionMediaIdTo = $bodyParams['albumSectionMediaIdTo'] ?? null;
        }

        $albumSectionMediaIdFrom = null;
        if (!isset($bodyParams['albumSectionMediaIdFrom'])) {
            $errors[] = [
                'field' => 'albumSectionMediaIdFrom',
                'message' => 'required'
            ];
        } else {
            $albumSectionMediaIdFrom = $bodyParams['albumSectionMediaIdFrom'] ?? null;
        }


        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->updateMediaSequenceForAlbumSection(
                $albumSectionId,
                $albumSectionMediaIdTo,
                $albumSectionMediaIdFrom,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: updateMediaSequenceForAlbumSection()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallUpdateAlbumSection(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['back', 'album-section', '{albumSectionId}'],
            $pathParts
        );
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        $albumSectionId = null;
        if (!isset($pathParams['albumSectionId'])) {
            $errors[] = [
                'field' => 'albumSectionId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['albumSectionId'])) {
                $errors[] = [
                    'field' => 'albumSectionId',
                    'message' => 'must be an integer'
                ];
            } else {
                $albumSectionId = intval($pathParams['albumSectionId']);
            }
        }

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $mainMediaId = $bodyParams['mainMediaId'] ?? null;
        $titleHtml = $bodyParams['titleHtml'] ?? null;
        $subtitleHtml = $bodyParams['subtitleHtml'] ?? null;
        $contentHtml = $bodyParams['contentHtml'] ?? null;
        $albumSectionIdSequenceTo = $bodyParams['albumSectionIdSequenceTo'] ?? null;

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->updateAlbumSection(
                $albumSectionId,
                $mainMediaId,
                $titleHtml,
                $subtitleHtml,
                $contentHtml,
                $albumSectionIdSequenceTo,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: updateAlbumSection()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallUpdateAlbumSectionMedia(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['back', 'album-section-media', '{albumSectionMediaId}'],
            $pathParts
        );
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        $albumSectionMediaId = null;
        if (!isset($pathParams['albumSectionMediaId'])) {
            $errors[] = [
                'field' => 'albumSectionMediaId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['albumSectionMediaId'])) {
                $errors[] = [
                    'field' => 'albumSectionMediaId',
                    'message' => 'must be an integer'
                ];
            } else {
                $albumSectionMediaId = intval($pathParams['albumSectionMediaId']);
            }
        }

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $captionHtml = $bodyParams['captionHtml'] ?? null;
        $sequence = $bodyParams['sequence'] ?? null;

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->updateAlbumSectionMedia(
                $albumSectionMediaId,
                $captionHtml,
                $sequence,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: updateAlbumSectionMedia()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallDeleteAlbumSectionMedia(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['back', 'album-section-media', '{albumSectionMediaId}'],
            $pathParts
        );
        $errors = [];

        $albumSectionMediaId = null;
        if (!isset($pathParams['albumSectionMediaId'])) {
            $errors[] = [
                'field' => 'albumSectionMediaId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['albumSectionMediaId'])) {
                $errors[] = [
                    'field' => 'albumSectionMediaId',
                    'message' => 'must be an integer'
                ];
            } else {
                $albumSectionMediaId = intval($pathParams['albumSectionMediaId']);
            }
        }

        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->deleteAlbumSectionMedia(
                $albumSectionMediaId,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: deleteAlbumSectionMedia()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }
   
    public function route(Request $request): ?Response
    {
        $auth = $this->authenticate($request);
        if ($auth !== true) {
            return Response::createUnauthorizedJsonResponse();
        }

        $pathParts = $request->pathWithoutLanguage;
        $method = $request->method;

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['back', 'session'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetSession($request);
        }

        if ($method === 'POST' &&
            $this->pathParamsMatcher(
                ['back', 'user'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallStoreUser($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['back', 'user'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetUsers($request);
        }

        if ($method === 'PUT' &&
            $pathParams = $this->pathParamsMatcher(
                ['back', 'user', '{userId}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallUpdateUser($request);
        }

        if ($method === 'DELETE' &&
            $pathParams = $this->pathParamsMatcher(
                ['back', 'user', '{userId}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallDestroyUser($request);
        }

        if ($method === 'POST' &&
            $this->pathParamsMatcher(
                ['back', 'article'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallStoreArticle($request);
        }

        if ($method === 'PUT' &&
            $pathParams = $this->pathParamsMatcher(
                ['back', 'article', '{articleId}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallUpdateArticle($request);
        }

        if ($method === 'DELETE' &&
            $pathParams = $this->pathParamsMatcher(
                ['back', 'article', '{articleId}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallDestroyArticle($request);
        }

        if ($method === 'POST' &&
            $this->pathParamsMatcher(
                ['back', 'tag'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallStoreTag($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['back', 'tag'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetTags($request);
        }

        if ($method === 'PUT' &&
            $pathParams = $this->pathParamsMatcher(
                ['back', 'content', '{contentId}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallUpdatePageContent($request);
        }

        if ($method === 'POST' &&
            $this->pathParamsMatcher(
                ['back', 'album'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallStoreAlbum($request);
        }

        if ($method === 'PUT' &&
            $pathParams = $this->pathParamsMatcher(
                ['back', 'album', '{albumId}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallUpdateAlbum($request);
        }

        if ($method === 'PUT' &&
            $pathParams = $this->pathParamsMatcher(
                ['back', 'album', '{albumId}', 'status', '{statusId}'],
                $pathParts,
                ['fixed', 'fixed', 'int', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallUpdateAlbumStatus($request);
        }

        if ($method === 'POST' &&
            $pathParams = $this->pathParamsMatcher(
                ['back', 'album', '{albumId}', 'section'],
                $pathParts,
                ['fixed', 'fixed', 'int', 'fixed']
            )
        ) {
            return $this->validateAndCallStoreAlbumSection($request);
        }

        if ($method === 'POST' &&
            $pathParams = $this->pathParamsMatcher(
                ['back', 'album-section', '{albumSectionId}', 'media'],
                $pathParts,
                ['fixed', 'fixed', 'int', 'fixed']
            )
        ) {
            return $this->validateAndCallStoreMediaForAlbumSection($request);
        }

        if ($method === 'PUT' &&
            $pathParams = $this->pathParamsMatcher(
                ['back', 'album-section', '{albumSectionId}', 'sequence'],
                $pathParts,
                ['fixed', 'fixed', 'int', 'fixed']
            )
        ) {
            return $this->validateAndCallUpdateMediaSequenceForAlbumSection($request);
        }

        if ($method === 'PUT' &&
            $pathParams = $this->pathParamsMatcher(
                ['back', 'album-section', '{albumSectionId}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallUpdateAlbumSection($request);
        }

        if ($method === 'PUT' &&
            $pathParams = $this->pathParamsMatcher(
                ['back', 'album-section-media', '{albumSectionMediaId}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallUpdateAlbumSectionMedia($request);
        }

        if ($method === 'DELETE' &&
            $pathParams = $this->pathParamsMatcher(
                ['back', 'album-section-media', '{albumSectionMediaId}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallDeleteAlbumSectionMedia($request);
        }

        return null;
    }
}
