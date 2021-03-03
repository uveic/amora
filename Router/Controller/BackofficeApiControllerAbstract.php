<?php

namespace Amora\Router;

use Throwable;
use Amora\Core\Core;
use Amora\Core\Model\Request;
use Amora\Core\Model\Response;
use Amora\Core\Util\StringUtil;

abstract class BackofficeApiControllerAbstract extends AbstractController
{
    public function __construct()
    {
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerStoreUserSuccessResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerStoreUserFailureResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerStoreUserUnauthorisedResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerUpdateUserSuccessResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerUpdateUserFailureResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerUpdateUserUnauthorisedResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerDestroyUserSuccessResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerDestroyUserFailureResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerStoreArticleSuccessResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerStoreArticleFailureResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerCheckArticleUriSuccessResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerCheckArticleUriFailureResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerUpdateArticleSuccessResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerUpdateArticleFailureResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerDestroyArticleSuccessResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerDestroyArticleFailureResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerStoreTagSuccessResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerStoreTagFailureResponse.php';
        require_once Core::getPathRoot() . '/Router/Controller/Response/BackofficeApiControllerGetTagsSuccessResponse.php';
    }

    abstract protected function authenticate(Request $request): bool;

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
    abstract protected function storeUser(
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
    ): Response;

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
     * @param bool|null $isEnabled
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
        ?int $languageId,
        ?int $roleId,
        ?string $timezone,
        ?bool $isEnabled,
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
     * @param int $statusId
     * @param int|null $typeId
     * @param string|null $title
     * @param string $contentHtml
     * @param string|null $uri
     * @param int|null $mainImageId
     * @param string|null $publishOn
     * @param array $sections
     * @param ?array $tags
     * @param Request $request
     * @return Response
     */
    abstract protected function storeArticle(
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
    ): Response;

    /**
     * Endpoint: /back/article/uri
     * Method: POST
     *
     * @param int|null $articleId
     * @param string $uri
     * @param Request $request
     * @return Response
     */
    abstract protected function checkArticleUri(
        ?int $articleId,
        string $uri,
        Request $request
    ): Response;

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
     * @param ?array $tags
     * @param Request $request
     * @return Response
     */
    abstract protected function updateArticle(
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

    private function validateAndCallStoreUser(Request $request)
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
            $name = isset($bodyParams['name'])
                ? $bodyParams['name']
                : null;
        }

        $email = null;
        if (!isset($bodyParams['email'])) {
            $errors[] = [
                'field' => 'email',
                'message' => 'required'
            ];
        } else {
            $email = isset($bodyParams['email'])
                ? $bodyParams['email']
                : null;
        }

        $bio = $bodyParams['bio'] ?? null;
        $languageId = null;
        if (!isset($bodyParams['languageId'])) {
            $errors[] = [
                'field' => 'languageId',
                'message' => 'required'
            ];
        } else {
            $languageId = isset($bodyParams['languageId'])
                ? $bodyParams['languageId']
                : null;
        }

        $roleId = null;
        if (!isset($bodyParams['roleId'])) {
            $errors[] = [
                'field' => 'roleId',
                'message' => 'required'
            ];
        } else {
            $roleId = isset($bodyParams['roleId'])
                ? $bodyParams['roleId']
                : null;
        }

        $timezone = null;
        if (!isset($bodyParams['timezone'])) {
            $errors[] = [
                'field' => 'timezone',
                'message' => 'required'
            ];
        } else {
            $timezone = isset($bodyParams['timezone'])
                ? $bodyParams['timezone']
                : null;
        }

        $isEnabled = null;
        if (!isset($bodyParams['isEnabled'])) {
            $errors[] = [
                'field' => 'isEnabled',
                'message' => 'required'
            ];
        } else {
            $isEnabled = isset($bodyParams['isEnabled'])
                ? $bodyParams['isEnabled']
                : null;
        }

        $newPassword = $bodyParams['newPassword'] ?? null;
        $repeatPassword = $bodyParams['repeatPassword'] ?? null;

        if (count($errors)) {
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
                $languageId,
                $roleId,
                $timezone,
                $isEnabled,
                $newPassword,
                $repeatPassword,
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

    private function validateAndCallUpdateUser(Request $request)
    {
        $pathParts = explode('/', $request->getPath());
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
        $languageId = $bodyParams['languageId'] ?? null;
        $roleId = $bodyParams['roleId'] ?? null;
        $timezone = $bodyParams['timezone'] ?? null;
        $isEnabled = $bodyParams['isEnabled'] ?? null;
        $currentPassword = $bodyParams['currentPassword'] ?? null;
        $newPassword = $bodyParams['newPassword'] ?? null;
        $repeatPassword = $bodyParams['repeatPassword'] ?? null;

        if (count($errors)) {
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
                $languageId,
                $roleId,
                $timezone,
                $isEnabled,
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

    private function validateAndCallDestroyUser(Request $request)
    {
        $pathParts = explode('/', $request->getPath());
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

        if (count($errors)) {
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

    private function validateAndCallStoreArticle(Request $request)
    {
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $statusId = null;
        if (!isset($bodyParams['statusId'])) {
            $errors[] = [
                'field' => 'statusId',
                'message' => 'required'
            ];
        } else {
            $statusId = isset($bodyParams['statusId'])
                ? $bodyParams['statusId']
                : null;
        }

        $typeId = $bodyParams['typeId'] ?? null;
        $title = $bodyParams['title'] ?? null;
        $contentHtml = null;
        if (!isset($bodyParams['contentHtml'])) {
            $errors[] = [
                'field' => 'contentHtml',
                'message' => 'required'
            ];
        } else {
            $contentHtml = isset($bodyParams['contentHtml'])
                ? $bodyParams['contentHtml']
                : null;
        }

        $uri = $bodyParams['uri'] ?? null;
        $mainImageId = $bodyParams['mainImageId'] ?? null;
        $publishOn = $bodyParams['publishOn'] ?? null;
        $sections = null;
        if (!isset($bodyParams['sections'])) {
            $errors[] = [
                'field' => 'sections',
                'message' => 'required'
            ];
        } else {
            $sections = isset($bodyParams['sections'])
                ? $bodyParams['sections']
                : null;
        }

        $tags = $bodyParams['tags'] ?? null;

        if (count($errors)) {
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
                $statusId,
                $typeId,
                $title,
                $contentHtml,
                $uri,
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

    private function validateAndCallCheckArticleUri(Request $request)
    {
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $articleId = $bodyParams['articleId'] ?? null;
        $uri = null;
        if (!isset($bodyParams['uri'])) {
            $errors[] = [
                'field' => 'uri',
                'message' => 'required'
            ];
        } else {
            $uri = isset($bodyParams['uri'])
                ? $bodyParams['uri']
                : null;
        }


        if (count($errors)) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->checkArticleUri(
                $articleId,
                $uri,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: checkArticleUri()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallUpdateArticle(Request $request)
    {
        $pathParts = explode('/', $request->getPath());
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

        $statusId = null;
        if (!isset($bodyParams['statusId'])) {
            $errors[] = [
                'field' => 'statusId',
                'message' => 'required'
            ];
        } else {
            $statusId = isset($bodyParams['statusId'])
                ? $bodyParams['statusId']
                : null;
        }

        $typeId = $bodyParams['typeId'] ?? null;
        $title = $bodyParams['title'] ?? null;
        $contentHtml = null;
        if (!isset($bodyParams['contentHtml'])) {
            $errors[] = [
                'field' => 'contentHtml',
                'message' => 'required'
            ];
        } else {
            $contentHtml = isset($bodyParams['contentHtml'])
                ? $bodyParams['contentHtml']
                : null;
        }

        $uri = $bodyParams['uri'] ?? null;
        $mainImageId = $bodyParams['mainImageId'] ?? null;
        $publishOn = $bodyParams['publishOn'] ?? null;
        $sections = null;
        if (!isset($bodyParams['sections'])) {
            $errors[] = [
                'field' => 'sections',
                'message' => 'required'
            ];
        } else {
            $sections = isset($bodyParams['sections'])
                ? $bodyParams['sections']
                : null;
        }

        $tags = $bodyParams['tags'] ?? null;

        if (count($errors)) {
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
                $statusId,
                $typeId,
                $title,
                $contentHtml,
                $uri,
                $mainImageId,
                $publishOn,
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

    private function validateAndCallDestroyArticle(Request $request)
    {
        $pathParts = explode('/', $request->getPath());
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

        if (count($errors)) {
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

    private function validateAndCallStoreTag(Request $request)
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
            $name = isset($bodyParams['name'])
                ? $bodyParams['name']
                : null;
        }


        if (count($errors)) {
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

    private function validateAndCallGetTags(Request $request)
    {
        $queryParams = $request->getGetParams();
        $errors = [];


        $name = isset($queryParams['name'])
            ? $queryParams['name']
            : null;
        if (count($errors)) {
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
   
    public function route(Request $request): Response
    {
        $auth = $this->authenticate($request);
        if ($auth !== true) {
            return Response::createUnauthorizedJsonResponse();
        }

        $path = $request->getPath();
        $pathParts = explode('/', $path);
        $method = $request->getMethod();

        if ($method === 'POST' &&
            $this->pathParamsMatcher(
                ['back', 'user'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallStoreUser($request);
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

        if ($method === 'POST' &&
            $this->pathParamsMatcher(
                ['back', 'article', 'uri'],
                $pathParts,
                ['fixed', 'fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallCheckArticleUri($request);
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

        return Response::createNotFoundResponse();
    }
}
