<?php

namespace Amora\Core\Router;

use Amora\Core\Core;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Router\AbstractController;
use Amora\Core\Router\RouterCore;
use Amora\Core\Util\StringUtil;
use Throwable;

abstract class PublicApiControllerAbstract extends AbstractController
{
    public function __construct()
    {
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerPingSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerLogErrorSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerTriggerEmailSenderJobSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerGetSessionSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerUserLoginSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerUserLoginFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerForgotPasswordSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerForgotPasswordFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerUserPasswordResetSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerUserPasswordResetFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerUserPasswordCreationSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerUserPasswordCreationFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerUserRegistrationSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerUserRegistrationFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerRequestRegistrationInviteSuccessResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerRequestRegistrationInviteFailureResponse.php';
        require_once Core::getPathRoot() . '/Core/Router/Controller/Response/PublicApiControllerGetBlogPostsSuccessResponse.php';
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
     * Endpoint: /papi/email
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function triggerEmailSenderJob(Request $request): Response;

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
     * @param string $languageIsoCode
     * @param Request $request
     * @return Response
     */
    abstract protected function userLogin(
        string $user,
        string $password,
        string $languageIsoCode,
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
     * @param string $languageIsoCode
     * @param Request $request
     * @return Response
     */
    abstract protected function userPasswordReset(
        int $userId,
        string $password,
        string $passwordConfirmation,
        string $verificationHash,
        string $languageIsoCode,
        Request $request
    ): Response;

    /**
     * Endpoint: /papi/login/password-creation
     * Method: POST
     *
     * @param int $userId
     * @param string $password
     * @param string $passwordConfirmation
     * @param string $verificationHash
     * @param string $verificationIdentifier
     * @param string $languageIsoCode
     * @param Request $request
     * @return Response
     */
    abstract protected function userPasswordCreation(
        int $userId,
        string $password,
        string $passwordConfirmation,
        string $verificationHash,
        string $verificationIdentifier,
        string $languageIsoCode,
        Request $request
    ): Response;

    /**
     * Endpoint: /papi/register
     * Method: POST
     *
     * @param string $languageIsoCode
     * @param string $email
     * @param string $password
     * @param string $name
     * @param string $timezone
     * @param Request $request
     * @return Response
     */
    abstract protected function userRegistration(
        string $languageIsoCode,
        string $email,
        string $password,
        string $name,
        string $timezone,
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

    /**
     * Endpoint: /papi/blog/post
     * Method: GET
     *
     * @param int $offset
     * @param int|null $itemsPerPage
     * @param Request $request
     * @return Response
     */
    abstract protected function getBlogPosts(
        int $offset,
        ?int $itemsPerPage,
        Request $request
    ): Response;

    private function validateAndCallPing(Request $request): Response
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

    private function validateAndCallLogError(Request $request): Response
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

    private function validateAndCallTriggerEmailSenderJob(Request $request): Response
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
            return $this->triggerEmailSenderJob(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicApiControllerAbstract - Method: triggerEmailSenderJob()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

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
                'Unexpected error in PublicApiControllerAbstract - Method: getSession()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallUserLogin(Request $request): Response
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
            $user = $bodyParams['user'] ?? null;
        }

        $password = null;
        if (!isset($bodyParams['password'])) {
            $errors[] = [
                'field' => 'password',
                'message' => 'required'
            ];
        } else {
            $password = $bodyParams['password'] ?? null;
        }

        $languageIsoCode = null;
        if (!isset($bodyParams['languageIsoCode'])) {
            $errors[] = [
                'field' => 'languageIsoCode',
                'message' => 'required'
            ];
        } else {
            $languageIsoCode = $bodyParams['languageIsoCode'] ?? null;
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
            return $this->userLogin(
                $user,
                $password,
                $languageIsoCode,
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

    private function validateAndCallForgotPassword(Request $request): Response
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
            $email = $bodyParams['email'] ?? null;
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

    private function validateAndCallUserPasswordReset(Request $request): Response
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
            $userId = $bodyParams['userId'] ?? null;
        }

        $password = null;
        if (!isset($bodyParams['password'])) {
            $errors[] = [
                'field' => 'password',
                'message' => 'required'
            ];
        } else {
            $password = $bodyParams['password'] ?? null;
        }

        $passwordConfirmation = null;
        if (!isset($bodyParams['passwordConfirmation'])) {
            $errors[] = [
                'field' => 'passwordConfirmation',
                'message' => 'required'
            ];
        } else {
            $passwordConfirmation = $bodyParams['passwordConfirmation'] ?? null;
        }

        $verificationHash = null;
        if (!isset($bodyParams['verificationHash'])) {
            $errors[] = [
                'field' => 'verificationHash',
                'message' => 'required'
            ];
        } else {
            $verificationHash = $bodyParams['verificationHash'] ?? null;
        }

        $languageIsoCode = null;
        if (!isset($bodyParams['languageIsoCode'])) {
            $errors[] = [
                'field' => 'languageIsoCode',
                'message' => 'required'
            ];
        } else {
            $languageIsoCode = $bodyParams['languageIsoCode'] ?? null;
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
            return $this->userPasswordReset(
                $userId,
                $password,
                $passwordConfirmation,
                $verificationHash,
                $languageIsoCode,
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

    private function validateAndCallUserPasswordCreation(Request $request): Response
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
            $userId = $bodyParams['userId'] ?? null;
        }

        $password = null;
        if (!isset($bodyParams['password'])) {
            $errors[] = [
                'field' => 'password',
                'message' => 'required'
            ];
        } else {
            $password = $bodyParams['password'] ?? null;
        }

        $passwordConfirmation = null;
        if (!isset($bodyParams['passwordConfirmation'])) {
            $errors[] = [
                'field' => 'passwordConfirmation',
                'message' => 'required'
            ];
        } else {
            $passwordConfirmation = $bodyParams['passwordConfirmation'] ?? null;
        }

        $verificationHash = null;
        if (!isset($bodyParams['verificationHash'])) {
            $errors[] = [
                'field' => 'verificationHash',
                'message' => 'required'
            ];
        } else {
            $verificationHash = $bodyParams['verificationHash'] ?? null;
        }

        $verificationIdentifier = null;
        if (!isset($bodyParams['verificationIdentifier'])) {
            $errors[] = [
                'field' => 'verificationIdentifier',
                'message' => 'required'
            ];
        } else {
            $verificationIdentifier = $bodyParams['verificationIdentifier'] ?? null;
        }

        $languageIsoCode = null;
        if (!isset($bodyParams['languageIsoCode'])) {
            $errors[] = [
                'field' => 'languageIsoCode',
                'message' => 'required'
            ];
        } else {
            $languageIsoCode = $bodyParams['languageIsoCode'] ?? null;
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
            return $this->userPasswordCreation(
                $userId,
                $password,
                $passwordConfirmation,
                $verificationHash,
                $verificationIdentifier,
                $languageIsoCode,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicApiControllerAbstract - Method: userPasswordCreation()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallUserRegistration(Request $request): Response
    {
        $bodyParams = $request->getBodyPayload();
        $errors = [];

        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => 'payload',
                'message' => 'required'
            ];
        }

        $languageIsoCode = null;
        if (!isset($bodyParams['languageIsoCode'])) {
            $errors[] = [
                'field' => 'languageIsoCode',
                'message' => 'required'
            ];
        } else {
            $languageIsoCode = $bodyParams['languageIsoCode'] ?? null;
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

        $password = null;
        if (!isset($bodyParams['password'])) {
            $errors[] = [
                'field' => 'password',
                'message' => 'required'
            ];
        } else {
            $password = $bodyParams['password'] ?? null;
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

        $timezone = null;
        if (!isset($bodyParams['timezone'])) {
            $errors[] = [
                'field' => 'timezone',
                'message' => 'required'
            ];
        } else {
            $timezone = $bodyParams['timezone'] ?? null;
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
            return $this->userRegistration(
                $languageIsoCode,
                $email,
                $password,
                $name,
                $timezone,
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

    private function validateAndCallRequestRegistrationInvite(Request $request): Response
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
            $email = $bodyParams['email'] ?? null;
        }

        $languageIsoCode = $bodyParams['languageIsoCode'] ?? null;

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

    private function validateAndCallGetBlogPosts(Request $request): Response
    {
        $queryParams = $request->getParams;
        $errors = [];

        $offset = null;
        if (!isset($queryParams['offset'])) {
            $errors[] = [
                'field' => 'offset',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($queryParams['offset'])) {
                $errors[] = [
                    'field' => 'offset',
                    'message' => 'must be an integer'
                ];
            } else {
                $offset = intval($queryParams['offset']);
            }
        }


        if (isset($queryParams['itemsPerPage']) && strlen($queryParams['itemsPerPage']) > 0) {
            $itemsPerPage = intval($queryParams['itemsPerPage']);
        } else {
            $itemsPerPage = null;
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
            return $this->getBlogPosts(
                $offset,
                $itemsPerPage,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicApiControllerAbstract - Method: getBlogPosts()' .
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
                ['papi', 'email'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallTriggerEmailSenderJob($request);
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
                ['papi', 'login', 'password-creation'],
                $pathParts,
                ['fixed', 'fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallUserPasswordCreation($request);
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

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['papi', 'blog', 'post'],
                $pathParts,
                ['fixed', 'fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetBlogPosts($request);
        }

        return null;
    }
}
