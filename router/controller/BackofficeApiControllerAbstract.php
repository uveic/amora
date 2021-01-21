<?php

namespace uve\router;

use Throwable;
use uve\core\Core;
use uve\core\model\Request;
use uve\core\model\Response;
use uve\core\util\StringUtil;

abstract class BackofficeApiControllerAbstract extends AbstractController
{
    public function __construct()
    {
        require_once Core::getPathRoot() . '/router/controller/response/BackofficeApiControllerStoreUserSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/BackofficeApiControllerStoreUserFailureResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/BackofficeApiControllerStoreUserForbiddenResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/BackofficeApiControllerUpdateUserSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/BackofficeApiControllerUpdateUserFailureResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/BackofficeApiControllerUpdateUserForbiddenResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/BackofficeApiControllerDeleteUserSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/BackofficeApiControllerStoreArticleSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/BackofficeApiControllerStoreArticleFailureResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/BackofficeApiControllerCheckArticleUriSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/BackofficeApiControllerCheckArticleUriFailureResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/BackofficeApiControllerUpdateArticleSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/BackofficeApiControllerUpdateArticleFailureResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/BackofficeApiControllerDestroyArticleSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/BackofficeApiControllerDestroyArticleFailureResponse.php';
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
    abstract protected function deleteUser(int $userId, Request $request): Response;

    /**
     * Endpoint: /back/article
     * Method: POST
     *
     * @param int $statusId
     * @param int|null $typeId
     * @param string $title
     * @param string $content
     * @param string $uri
     * @param string|null $mainImageSrc
     * @param array $sections
     * @param Request $request
     * @return Response
     */
    abstract protected function storeArticle(
        int $statusId,
        ?int $typeId,
        string $title,
        string $content,
        string $uri,
        ?string $mainImageSrc,
        array $sections,
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
     * @param string|null $content
     * @param string $uri
     * @param string|null $mainImageSrc
     * @param array $sections
     * @param Request $request
     * @return Response
     */
    abstract protected function updateArticle(
        int $articleId,
        int $statusId,
        ?int $typeId,
        ?string $title,
        ?string $content,
        string $uri,
        ?string $mainImageSrc,
        array $sections,
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

    private function validateAndCallDeleteUser(Request $request)
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
            return $this->deleteUser(
                $userId,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeApiControllerAbstract - Method: deleteUser()' .
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
        $title = null;
        if (!isset($bodyParams['title'])) {
            $errors[] = [
                'field' => 'title',
                'message' => 'required'
            ];
        } else {
            $title = isset($bodyParams['title'])
                ? $bodyParams['title']
                : null;
        }

        $content = null;
        if (!isset($bodyParams['content'])) {
            $errors[] = [
                'field' => 'content',
                'message' => 'required'
            ];
        } else {
            $content = isset($bodyParams['content'])
                ? $bodyParams['content']
                : null;
        }

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

        $mainImageSrc = $bodyParams['mainImageSrc'] ?? null;
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
                $content,
                $uri,
                $mainImageSrc,
                $sections,
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
        $content = $bodyParams['content'] ?? null;
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

        $mainImageSrc = $bodyParams['mainImageSrc'] ?? null;
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
                $content,
                $uri,
                $mainImageSrc,
                $sections,
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
   
    public function route(Request $request): Response
    {
        $auth = $this->authenticate($request);
        if ($auth !== true) {
            return Response::createUnauthorizedPlainTextResponse();
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
            return $this->validateAndCallDeleteUser($request);
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

        return Response::createNotFoundResponse();
    }
}
