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
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerGetPreviousPathsForArticleSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerGetPreviousPathsForArticleFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerStoreTagSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerStoreTagFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerGetTagsSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerUpdatePageContentSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/BackofficeApiControllerUpdatePageContentFailureResponse.php';
    }

    abstract protected function authenticate(Request $request): bool;

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
     * @param string|null $path
     * @param int|null $mainImageId
     * @param string|null $publishOn
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
        ?string $path,
        ?int $mainImageId,
        ?string $publishOn,
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
     * @param string|null $path
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
        ?string $path,
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
     * Endpoint: /back/article/{articleId}/previous-path
     * Method: GET
     *
     * @param int $articleId
     * @param Request $request
     * @return Response
     */
    abstract protected function getPreviousPathsForArticle(
        int $articleId,
        Request $request
    ): Response;

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
     * @param string|null $titleHtml
     * @param string|null $subtitleHtml
     * @param string $contentHtml
     * @param int|null $mainImageId
     * @param Request $request
     * @return Response
     */
    abstract protected function updatePageContent(
        int $contentId,
        ?string $titleHtml,
        ?string $subtitleHtml,
        string $contentHtml,
        ?int $mainImageId,
        Request $request
    ): Response;

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

        $path = $bodyParams['path'] ?? null;
        $mainImageId = $bodyParams['mainImageId'] ?? null;
        $publishOn = $bodyParams['publishOn'] ?? null;
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
                $path,
                $mainImageId,
                $publishOn,
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

        $path = $bodyParams['path'] ?? null;
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
                $path,
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

    private function validateAndCallGetPreviousPathsForArticle(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['back', 'article', '{articleId}', 'previous-path'],
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
            return $this->getPreviousPathsForArticle(
                $articleId,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: getPreviousPathsForArticle()' .
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

        $titleHtml = $bodyParams['titleHtml'] ?? null;
        $subtitleHtml = $bodyParams['subtitleHtml'] ?? null;
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
                $titleHtml,
                $subtitleHtml,
                $contentHtml,
                $mainImageId,
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
   
    public function route(Request $request): ?Response
    {
        $auth = $this->authenticate($request);
        if ($auth !== true) {
            return Response::createUnauthorizedJsonResponse();
        }

        $pathParts = $request->pathWithoutLanguage;
        $method = $request->method;

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

        if ($method === 'GET' &&
            $pathParams = $this->pathParamsMatcher(
                ['back', 'article', '{articleId}', 'previous-path'],
                $pathParts,
                ['fixed', 'fixed', 'int', 'fixed']
            )
        ) {
            return $this->validateAndCallGetPreviousPathsForArticle($request);
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

        return null;
    }
}
