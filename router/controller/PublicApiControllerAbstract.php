<?php

namespace uve\router;

use Throwable;
use uve\core\Core;
use uve\core\model\Request;
use uve\core\model\Response;
use uve\core\util\StringUtil;

abstract class PublicApiControllerAbstract extends AbstractController
{
    public function __construct()
    {
        require_once Core::getPathRoot() . '/router/controller/response/PublicApiControllerPingSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicApiControllerLogErrorSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicApiControllerGetSessionSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicApiControllerUserLoginSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicApiControllerUserLoginFailureResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicApiControllerForgotPasswordSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicApiControllerForgotPasswordFailureResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicApiControllerUserPasswordResetSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicApiControllerUserPasswordResetFailureResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicApiControllerUserRegistrationSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicApiControllerUserRegistrationFailureResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicApiControllerRequestRegistrationInviteSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicApiControllerRequestRegistrationInviteFailureResponse.php';
    }

    abstract protected function authenticate(Request $request): bool;

    /**
     * Endpoint: /papi/ping
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function ping(Request $request): Response;

    /**
     * Endpoint: /papi/log
     * Method: POST
     *
     * @param string|null $endpoint
     * @param string|null $method
     * @param string|null $payload
     * @param string|null $errorMessage
     * @param string|null $userAgent
     * @param string|null $pageUrl
     * @param Request $request
     * @return Response
     */
    abstract protected function logError(
        ?string $endpoint,
        ?string $method,
        ?string $payload,
        ?string $errorMessage,
        ?string $userAgent,
        ?string $pageUrl,
        Request $request
    ): Response;

    /**
     * Endpoint: /papi/session
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getSession(Request $request): Response;

    /**
     * Endpoint: /papi/login
     * Method: POST
     *
     * @param string $user
     * @param string $password
     * @param Request $request
     * @return Response
     */
    abstract protected function userLogin(
        string $user,
        string $password,
        Request $request
    ): Response;

    /**
     * Endpoint: /papi/login/forgot
     * Method: POST
     *
     * @param string $email
     * @param Request $request
     * @return Response
     */
    abstract protected function forgotPassword(string $email, Request $request): Response;

    /**
     * Endpoint: /papi/login/password-reset
     * Method: POST
     *
     * @param int $userId
     * @param string $password
     * @param string $passwordConfirmation
     * @param string $verificationHash
     * @param Request $request
     * @return Response
     */
    abstract protected function userPasswordReset(
        int $userId,
        string $password,
        string $passwordConfirmation,
        string $verificationHash,
        Request $request
    ): Response;

    /**
     * Endpoint: /papi/register
     * Method: POST
     *
     * @param string $email
     * @param string $password
     * @param string $name
     * @param int $timezoneOffsetMinutes
     * @param Request $request
     * @return Response
     */
    abstract protected function userRegistration(
        string $email,
        string $password,
        string $name,
        int $timezoneOffsetMinutes,
        Request $request
    ): Response;

    /**
     * Endpoint: /papi/invite-request
     * Method: POST
     *
     * @param string $email
     * @param string|null $languageIsoCode
     * @param Request $request
     * @return Response
     */
    abstract protected function requestRegistrationInvite(
        string $email,
        ?string $languageIsoCode,
        Request $request
    ): Response;

    private function validateAndCallPing(Request $request)
    {
        $errors = [];

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
            return $this->ping(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicApiControllerAbstract - Method: ping()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallLogError(Request $request)
    {
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $endpoint = $bodyParams['endpoint'] ?? null;
        $method = $bodyParams['method'] ?? null;
        $payload = $bodyParams['payload'] ?? null;
        $errorMessage = $bodyParams['errorMessage'] ?? null;
        $userAgent = $bodyParams['userAgent'] ?? null;
        $pageUrl = $bodyParams['pageUrl'] ?? null;

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
            return $this->logError(
                $endpoint,
                $method,
                $payload,
                $errorMessage,
                $userAgent,
                $pageUrl,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicApiControllerAbstract - Method: logError()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetSession(Request $request)
    {
        $errors = [];

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
            return $this->getSession(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicApiControllerAbstract - Method: getSession()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallUserLogin(Request $request)
    {
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $user = null;
        if (!isset($bodyParams['user'])) {
            $errors[] = [
                'field' => 'user',
                'message' => 'required'
            ];
        } else {
            $user = isset($bodyParams['user'])
                ? $bodyParams['user']
                : null;
        }

        $password = null;
        if (!isset($bodyParams['password'])) {
            $errors[] = [
                'field' => 'password',
                'message' => 'required'
            ];
        } else {
            $password = isset($bodyParams['password'])
                ? $bodyParams['password']
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
            return $this->userLogin(
                $user,
                $password,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicApiControllerAbstract - Method: userLogin()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallForgotPassword(Request $request)
    {
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
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
            return $this->forgotPassword(
                $email,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicApiControllerAbstract - Method: forgotPassword()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallUserPasswordReset(Request $request)
    {
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $userId = null;
        if (!isset($bodyParams['userId'])) {
            $errors[] = [
                'field' => 'userId',
                'message' => 'required'
            ];
        } else {
            $userId = isset($bodyParams['userId'])
                ? $bodyParams['userId']
                : null;
        }

        $password = null;
        if (!isset($bodyParams['password'])) {
            $errors[] = [
                'field' => 'password',
                'message' => 'required'
            ];
        } else {
            $password = isset($bodyParams['password'])
                ? $bodyParams['password']
                : null;
        }

        $passwordConfirmation = null;
        if (!isset($bodyParams['passwordConfirmation'])) {
            $errors[] = [
                'field' => 'passwordConfirmation',
                'message' => 'required'
            ];
        } else {
            $passwordConfirmation = isset($bodyParams['passwordConfirmation'])
                ? $bodyParams['passwordConfirmation']
                : null;
        }

        $verificationHash = null;
        if (!isset($bodyParams['verificationHash'])) {
            $errors[] = [
                'field' => 'verificationHash',
                'message' => 'required'
            ];
        } else {
            $verificationHash = isset($bodyParams['verificationHash'])
                ? $bodyParams['verificationHash']
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
            return $this->userPasswordReset(
                $userId,
                $password,
                $passwordConfirmation,
                $verificationHash,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicApiControllerAbstract - Method: userPasswordReset()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallUserRegistration(Request $request)
    {
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
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

        $password = null;
        if (!isset($bodyParams['password'])) {
            $errors[] = [
                'field' => 'password',
                'message' => 'required'
            ];
        } else {
            $password = isset($bodyParams['password'])
                ? $bodyParams['password']
                : null;
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

        $timezoneOffsetMinutes = null;
        if (!isset($bodyParams['timezoneOffsetMinutes'])) {
            $errors[] = [
                'field' => 'timezoneOffsetMinutes',
                'message' => 'required'
            ];
        } else {
            $timezoneOffsetMinutes = isset($bodyParams['timezoneOffsetMinutes'])
                ? $bodyParams['timezoneOffsetMinutes']
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
            return $this->userRegistration(
                $email,
                $password,
                $name,
                $timezoneOffsetMinutes,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicApiControllerAbstract - Method: userRegistration()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallRequestRegistrationInvite(Request $request)
    {
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
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

        $languageIsoCode = $bodyParams['languageIsoCode'] ?? null;

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
            return $this->requestRegistrationInvite(
                $email,
                $languageIsoCode,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicApiControllerAbstract - Method: requestRegistrationInvite()' .
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

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['papi', 'ping'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallPing($request);
        }

        if ($method === 'POST' &&
            $this->pathParamsMatcher(
                ['papi', 'log'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallLogError($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['papi', 'session'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetSession($request);
        }

        if ($method === 'POST' &&
            $this->pathParamsMatcher(
                ['papi', 'login'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallUserLogin($request);
        }

        if ($method === 'POST' &&
            $this->pathParamsMatcher(
                ['papi', 'login', 'forgot'],
                $pathParts,
                ['fixed', 'fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallForgotPassword($request);
        }

        if ($method === 'POST' &&
            $this->pathParamsMatcher(
                ['papi', 'login', 'password-reset'],
                $pathParts,
                ['fixed', 'fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallUserPasswordReset($request);
        }

        if ($method === 'POST' &&
            $this->pathParamsMatcher(
                ['papi', 'register'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallUserRegistration($request);
        }

        if ($method === 'POST' &&
            $this->pathParamsMatcher(
                ['papi', 'invite-request'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallRequestRegistrationInvite($request);
        }

        return Response::createNotFoundResponse();
    }
}
