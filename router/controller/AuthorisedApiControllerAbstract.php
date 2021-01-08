<?php

namespace uve\router;

use Throwable;
use uve\core\Core;
use uve\core\model\Request;
use uve\core\model\Response;
use uve\core\util\StringUtil;

abstract class AuthorisedApiControllerAbstract extends AbstractController
{
    public function __construct()
    {
        require_once Core::getPathRoot() . '/router/controller/response/AuthorisedApiControllerStoreImageSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/AuthorisedApiControllerStoreImageFailureResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/AuthorisedApiControllerStoreImageForbiddenResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/AuthorisedApiControllerDestroyImageSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/AuthorisedApiControllerUpdateUserAccountSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/AuthorisedApiControllerUpdateUserAccountForbiddenResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/AuthorisedApiControllerSendVerificationEmailSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/AuthorisedApiControllerSendVerificationEmailForbiddenResponse.php';
    }

    abstract protected function authenticate(Request $request): bool;

    /**
     * Endpoint: /api/image
     * Method: POST
     *
     * @param int|null $articleId
     * @param Request $request
     * @return Response
     */
    abstract protected function storeImage(?int $articleId, Request $request): Response;

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
     * @param string|null $languageId
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
        ?string $languageId,
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

    private function validateAndCallStoreImage(Request $request)
    {
        $formDataParams = $request->getPostParams();
        $errors = [];


        if (isset($formDataParams['articleId']) && strlen($formDataParams['articleId']) > 0) {
            $articleId = intval($formDataParams['articleId']);
        } else {
            $articleId = null;
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
            return $this->storeImage(
                $articleId,
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

    private function validateAndCallDestroyImage(Request $request)
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

    private function validateAndCallUpdateUserAccount(Request $request)
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
        $languageId = $bodyParams['languageId'] ?? null;
        $timezone = $bodyParams['timezone'] ?? null;
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
            return $this->updateUserAccount(
                $userId,
                $name,
                $email,
                $languageId,
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

    private function validateAndCallSendVerificationEmail(Request $request)
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

        return Response::createNotFoundResponse();
    }
}
