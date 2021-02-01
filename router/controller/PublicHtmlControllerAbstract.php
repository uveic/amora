<?php

namespace uve\router;

use Throwable;
use uve\core\Core;
use uve\core\model\Request;
use uve\core\model\Response;
use uve\core\util\StringUtil;

abstract class PublicHtmlControllerAbstract extends AbstractController
{
    public function __construct()
    {
        require_once Core::getPathRoot() . '/router/controller/response/PublicHtmlControllerGetHomePageSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicHtmlControllerGetLoginPageSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicHtmlControllerGetForgotPasswordPageSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicHtmlControllerGetRegistrationPageSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicHtmlControllerGetUserVerifiedHtmlSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicHtmlControllerGetPasswordChangeHtmlSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/PublicHtmlControllerGetInviteRequestPageSuccessResponse.php';
    }

    abstract protected function authenticate(Request $request): bool;

    /**
     * Endpoint: /home
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getHomePage(Request $request): Response;

    /**
     * Endpoint: /login
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getLoginPage(Request $request): Response;

    /**
     * Endpoint: /login/forgot
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getForgotPasswordPage(Request $request): Response;

    /**
     * Endpoint: /register
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getRegistrationPage(Request $request): Response;

    /**
     * Endpoint: /user/verify/{verificationIdentifier}
     * Method: GET
     *
     * @param string $verificationIdentifier
     * @param Request $request
     * @return Response
     */
    abstract protected function getUserVerifiedHtml(
        string $verificationIdentifier,
        Request $request
    ): Response;

    /**
     * Endpoint: /user/reset/{verificationIdentifier}
     * Method: GET
     *
     * @param string $verificationIdentifier
     * @param Request $request
     * @return Response
     */
    abstract protected function getPasswordChangeHtml(
        string $verificationIdentifier,
        Request $request
    ): Response;

    /**
     * Endpoint: /invite-request
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getInviteRequestPage(Request $request): Response;

    private function validateAndCallGetHomePage(Request $request)
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
            return $this->getHomePage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicHtmlControllerAbstract - Method: getHomePage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetLoginPage(Request $request)
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
            return $this->getLoginPage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicHtmlControllerAbstract - Method: getLoginPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetForgotPasswordPage(Request $request)
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
            return $this->getForgotPasswordPage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicHtmlControllerAbstract - Method: getForgotPasswordPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetRegistrationPage(Request $request)
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
            return $this->getRegistrationPage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicHtmlControllerAbstract - Method: getRegistrationPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetUserVerifiedHtml(Request $request)
    {
        $pathParts = explode('/', $request->getPath());
        $pathParams = $this->getPathParams(
            ['user', 'verify', '{verificationIdentifier}'],
            $pathParts
        );
        $errors = [];

        $verificationIdentifier = null;
        if (!isset($pathParams['verificationIdentifier'])) {
            $errors[] = [
                'field' => 'verificationIdentifier',
                'message' => 'required'
            ];
        } else {
            $verificationIdentifier = isset($pathParams['verificationIdentifier'])
                ? $pathParams['verificationIdentifier']
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
            return $this->getUserVerifiedHtml(
                $verificationIdentifier,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicHtmlControllerAbstract - Method: getUserVerifiedHtml()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetPasswordChangeHtml(Request $request)
    {
        $pathParts = explode('/', $request->getPath());
        $pathParams = $this->getPathParams(
            ['user', 'reset', '{verificationIdentifier}'],
            $pathParts
        );
        $errors = [];

        $verificationIdentifier = null;
        if (!isset($pathParams['verificationIdentifier'])) {
            $errors[] = [
                'field' => 'verificationIdentifier',
                'message' => 'required'
            ];
        } else {
            $verificationIdentifier = isset($pathParams['verificationIdentifier'])
                ? $pathParams['verificationIdentifier']
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
            return $this->getPasswordChangeHtml(
                $verificationIdentifier,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicHtmlControllerAbstract - Method: getPasswordChangeHtml()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetInviteRequestPage(Request $request)
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
            return $this->getInviteRequestPage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicHtmlControllerAbstract - Method: getInviteRequestPage()' .
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
            return Response::createRedirectResponse('/login');
        }

        $path = $request->getPath();
        $pathParts = explode('/', $path);
        $method = $request->getMethod();

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['home'],
                $pathParts,
                ['fixed']
            )
        ) {
            return $this->validateAndCallGetHomePage($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['login'],
                $pathParts,
                ['fixed']
            )
        ) {
            return $this->validateAndCallGetLoginPage($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['login', 'forgot'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetForgotPasswordPage($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['register'],
                $pathParts,
                ['fixed']
            )
        ) {
            return $this->validateAndCallGetRegistrationPage($request);
        }

        if ($method === 'GET' &&
            $pathParams = $this->pathParamsMatcher(
                ['user', 'verify', '{verificationIdentifier}'],
                $pathParts,
                ['fixed', 'fixed', 'string']
            )
        ) {
            return $this->validateAndCallGetUserVerifiedHtml($request);
        }

        if ($method === 'GET' &&
            $pathParams = $this->pathParamsMatcher(
                ['user', 'reset', '{verificationIdentifier}'],
                $pathParts,
                ['fixed', 'fixed', 'string']
            )
        ) {
            return $this->validateAndCallGetPasswordChangeHtml($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['invite-request'],
                $pathParts,
                ['fixed']
            )
        ) {
            return $this->validateAndCallGetInviteRequestPage($request);
        }

        return Response::createNotFoundResponse();
    }
}
