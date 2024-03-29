<?php

namespace Amora\Core\Router;

use Amora\Core\Core;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Router\AbstractController;
use Amora\Core\Router\RouterCore;
use Amora\Core\Util\StringUtil;
use Throwable;

abstract class AuthorisedHtmlControllerAbstract extends AbstractController
{
    public function __construct()
    {

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

    private function validateAndCallLogout(Request $request): Response
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

    private function validateAndCallGetUserAccountHtml(Request $request): Response
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

    private function validateAndCallGetUserAccountSettingsHtml(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
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
            $settingsPage = $pathParams['settingsPage'] ?? null;
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
   
    public function route(Request $request): ?Response
    {
        $auth = $this->authenticate($request);
        if ($auth !== true) {
            return Response::createUnauthorisedRedirectLoginResponse($request->siteLanguage);
        }

        $pathParts = $request->pathWithoutLanguage;
        $method = $request->method;

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

        return null;
    }
}
