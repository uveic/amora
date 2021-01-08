<?php

namespace uve\router;

use Throwable;
use uve\core\Core;
use uve\core\model\Request;
use uve\core\model\Response;
use uve\core\util\StringUtil;

abstract class AuthorisedHtmlControllerAbstract extends AbstractController
{
    public function __construct()
    {
        require_once Core::getPathRoot() . '/router/controller/response/AuthorisedHtmlControllerLogoutSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/AuthorisedHtmlControllerGetUserAccountHtmlSuccessResponse.php';
        require_once Core::getPathRoot() . '/router/controller/response/AuthorisedHtmlControllerGetUserAccountSettingsHtmlSuccessResponse.php';
    }

    abstract protected function authenticate(Request $request): bool;

    /**
     * Endpoint: /logout
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function logout(Request $request): Response;

    /**
     * Endpoint: /account
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getUserAccountHtml(Request $request): Response;

    /**
     * Endpoint: /account/{settingsPage}
     * Method: GET
     *
     * @param string $settingsPage
     * @param Request $request
     * @return Response
     */
    abstract protected function getUserAccountSettingsHtml(
        string $settingsPage,
        Request $request
    ): Response;

    private function validateAndCallLogout(Request $request)
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
            return $this->logout(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in AuthorisedHtmlControllerAbstract - Method: logout()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetUserAccountHtml(Request $request)
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
            return $this->getUserAccountHtml(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in AuthorisedHtmlControllerAbstract - Method: getUserAccountHtml()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetUserAccountSettingsHtml(Request $request)
    {
        $pathParts = explode('/', $request->getPath());
        $pathParams = $this->getPathParams(
            ['account', '{settingsPage}'],
            $pathParts
        );
        $errors = [];

        $settingsPage = null;
        if (!isset($pathParams['settingsPage'])) {
            $errors[] = [
                'field' => 'settingsPage',
                'message' => 'required'
            ];
        } else {
            $settingsPage = isset($pathParams['settingsPage'])
                ? $pathParams['settingsPage']
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
            return $this->getUserAccountSettingsHtml(
                $settingsPage,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in AuthorisedHtmlControllerAbstract - Method: getUserAccountSettingsHtml()' .
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
                ['logout'],
                $pathParts,
                ['fixed']
            )
        ) {
            return $this->validateAndCallLogout($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['account'],
                $pathParts,
                ['fixed']
            )
        ) {
            return $this->validateAndCallGetUserAccountHtml($request);
        }

        if ($method === 'GET' &&
            $pathParams = $this->pathParamsMatcher(
                ['account', '{settingsPage}'],
                $pathParts,
                ['fixed', 'string']
            )
        ) {
            return $this->validateAndCallGetUserAccountSettingsHtml($request);
        }

        return Response::createNotFoundResponse();
    }
}
