<?php

namespace Amora\Core\Router;

use Amora\Core\Core;
use Amora\Core\Model\Request;
use Amora\Core\Model\Response;
use Amora\Core\Router\AbstractController;
use Amora\Core\Router\RouterCore;
use Amora\Core\Util\StringUtil;
use Throwable;

abstract class AuthorisedApiControllerAbstract extends AbstractController
{
    public function __construct()
    {
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerStoreFileSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerStoreFileFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerStoreFileUnauthorisedResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerGetFileSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerGetFileFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerGetFileUnauthorisedResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerDestroyFileSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerDestroyFileFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerDestroyFileUnauthorisedResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerStoreImageSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerStoreImageFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerStoreImageUnauthorisedResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerDestroyImageSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerDestroyImageFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerDestroyImageUnauthorisedResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerUpdateUserAccountSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerUpdateUserAccountFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerUpdateUserAccountUnauthorisedResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerSendVerificationEmailSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerSendVerificationEmailFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/AuthorisedApiControllerSendVerificationEmailUnauthorisedResponse.php';
    }

    abstract protected function authenticate(Request $request): bool;

    /**
     * Endpoint: /api/file
     * Method: POST
     *
     * @param int $fileTypeId
     * @param Request $request
     * @return Response
     */
    abstract protected function storeFile(int $fileTypeId, Request $request): Response;

    /**
     * Endpoint: /api/file/{id}
     * Method: GET
     *
     * @param int $id
     * @param Request $request
     * @return Response
     */
    abstract protected function getFile(int $id, Request $request): Response;

    /**
     * Endpoint: /api/file/{id}
     * Method: DELETE
     *
     * @param int $id
     * @param Request $request
     * @return Response
     */
    abstract protected function destroyFile(int $id, Request $request): Response;

    /**
     * Endpoint: /api/image
     * Method: POST
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function storeImage(Request $request): Response;

    /**
     * Endpoint: /api/image/{imageId}
     * Method: DELETE
     *
     * @param int $imageId
     * @param int|null $eventId
     * @param Request $request
     * @return Response
     */
    abstract protected function destroyImage(
        int $imageId,
        ?int $eventId,
        Request $request
    ): Response;

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
    abstract protected function updateUserAccount(
        int $userId,
        ?string $name,
        ?string $email,
        ?string $languageIsoCode,
        ?string $timezone,
        ?string $currentPassword,
        ?string $newPassword,
        ?string $repeatPassword,
        Request $request
    ): Response;

    /**
     * Endpoint: /api/user/{userId}/verification-email
     * Method: POST
     *
     * @param int $userId
     * @param Request $request
     * @return Response
     */
    abstract protected function sendVerificationEmail(int $userId, Request $request): Response;

    private function validateAndCallStoreFile(Request $request): Response
    {
        $formDataParams = $request->postParams;
        $errors = [];

        $fileTypeId = null;
        if (!isset($formDataParams['fileTypeId'])) {
            $errors[] = [
                'field' => 'fileTypeId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($formDataParams['fileTypeId'])) {
                $errors[] = [
                    'field' => 'fileTypeId',
                    'message' => 'must be an integer'
                ];
            } else {
                $fileTypeId = intval($formDataParams['fileTypeId']);
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
            return $this->storeFile(
                $fileTypeId,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in AuthorisedApiControllerAbstract - Method: storeFile()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetFile(Request $request): Response
    {
        $pathParts = explode('/', $request->getPath());
        $pathParams = $this->getPathParams(
            ['api', 'file', '{id}'],
            $pathParts
        );
        $errors = [];

        $id = null;
        if (!isset($pathParams['id'])) {
            $errors[] = [
                'field' => 'id',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['id'])) {
                $errors[] = [
                    'field' => 'id',
                    'message' => 'must be an integer'
                ];
            } else {
                $id = intval($pathParams['id']);
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
            return $this->getFile(
                $id,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in AuthorisedApiControllerAbstract - Method: getFile()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallDestroyFile(Request $request): Response
    {
        $pathParts = explode('/', $request->getPath());
        $pathParams = $this->getPathParams(
            ['api', 'file', '{id}'],
            $pathParts
        );
        $errors = [];

        $id = null;
        if (!isset($pathParams['id'])) {
            $errors[] = [
                'field' => 'id',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['id'])) {
                $errors[] = [
                    'field' => 'id',
                    'message' => 'must be an integer'
                ];
            } else {
                $id = intval($pathParams['id']);
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
            return $this->destroyFile(
                $id,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in AuthorisedApiControllerAbstract - Method: destroyFile()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallStoreImage(Request $request): Response
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
            return $this->storeImage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in AuthorisedApiControllerAbstract - Method: storeImage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallDestroyImage(Request $request): Response
    {
        $pathParts = explode('/', $request->getPath());
        $pathParams = $this->getPathParams(
            ['api', 'image', '{imageId}'],
            $pathParts
        );
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        $imageId = null;
        if (!isset($pathParams['imageId'])) {
            $errors[] = [
                'field' => 'imageId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['imageId'])) {
                $errors[] = [
                    'field' => 'imageId',
                    'message' => 'must be an integer'
                ];
            } else {
                $imageId = intval($pathParams['imageId']);
            }
        }

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $eventId = $bodyParams['eventId'] ?? null;

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
            return $this->destroyImage(
                $imageId,
                $eventId,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in AuthorisedApiControllerAbstract - Method: destroyImage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallUpdateUserAccount(Request $request): Response
    {
        $pathParts = explode('/', $request->getPath());
        $pathParams = $this->getPathParams(
            ['api', 'user', '{userId}'],
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
        $languageIsoCode = $bodyParams['languageIsoCode'] ?? null;
        $timezone = $bodyParams['timezone'] ?? null;
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
            return $this->updateUserAccount(
                $userId,
                $name,
                $email,
                $languageIsoCode,
                $timezone,
                $currentPassword,
                $newPassword,
                $repeatPassword,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in AuthorisedApiControllerAbstract - Method: updateUserAccount()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallSendVerificationEmail(Request $request): Response
    {
        $pathParts = explode('/', $request->getPath());
        $pathParams = $this->getPathParams(
            ['api', 'user', '{userId}', 'verification-email'],
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
            return $this->sendVerificationEmail(
                $userId,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in AuthorisedApiControllerAbstract - Method: sendVerificationEmail()' .
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

        $path = $request->getPath();
        $pathParts = explode('/', $path);
        $method = $request->method;

        if ($method === 'POST' &&
            $this->pathParamsMatcher(
                ['api', 'file'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallStoreFile($request);
        }

        if ($method === 'GET' &&
            $pathParams = $this->pathParamsMatcher(
                ['api', 'file', '{id}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallGetFile($request);
        }

        if ($method === 'DELETE' &&
            $pathParams = $this->pathParamsMatcher(
                ['api', 'file', '{id}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallDestroyFile($request);
        }

        if ($method === 'POST' &&
            $this->pathParamsMatcher(
                ['api', 'image'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallStoreImage($request);
        }

        if ($method === 'DELETE' &&
            $pathParams = $this->pathParamsMatcher(
                ['api', 'image', '{imageId}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallDestroyImage($request);
        }

        if ($method === 'PUT' &&
            $pathParams = $this->pathParamsMatcher(
                ['api', 'user', '{userId}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallUpdateUserAccount($request);
        }

        if ($method === 'POST' &&
            $pathParams = $this->pathParamsMatcher(
                ['api', 'user', '{userId}', 'verification-email'],
                $pathParts,
                ['fixed', 'fixed', 'int', 'fixed']
            )
        ) {
            return $this->validateAndCallSendVerificationEmail($request);
        }

        return null;
    }
}
