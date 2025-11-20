<?php

namespace Amora\Core\Router;

use Amora\Core\Core;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Router\AbstractController;
use Amora\Core\Router\RouterCore;
use Amora\Core\Util\StringUtil;
use Throwable;

abstract class PublicHtmlControllerAbstract extends AbstractController
{
    public function __construct()
    {

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
     * Endpoint: /user/create/{verificationIdentifier}
     * Method: GET
     *
     * @param string $verificationIdentifier
     * @param Request $request
     * @return Response
     */
    abstract protected function getCreateUserPasswordHtml(
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

    /**
     * Endpoint: /album/{albumSlug}
     * Method: GET
     *
     * @param string $albumSlug
     * @param Request $request
     * @return Response
     */
    abstract protected function getAlbumPage(string $albumSlug, Request $request): Response;

    /**
     * Endpoint: /click/{identifier}
     * Method: GET
     *
     * @param string $identifier
     * @param Request $request
     * @return Response
     */
    abstract protected function logEventClick(string $identifier, Request $request): Response;

    /**
     * Endpoint: /json-feed
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getJsonFeed(Request $request): Response;

    /**
     * Endpoint: /rss
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getRss(Request $request): Response;

    /**
     * Endpoint: /sitemap
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getSitemap(Request $request): Response;

    private function validateAndCallGetHomePage(Request $request): Response
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

    private function validateAndCallGetLoginPage(Request $request): Response
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

    private function validateAndCallGetForgotPasswordPage(Request $request): Response
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

    private function validateAndCallGetRegistrationPage(Request $request): Response
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

    private function validateAndCallGetUserVerifiedHtml(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
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
            $verificationIdentifier = $pathParams['verificationIdentifier'] ?? null;
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

    private function validateAndCallGetPasswordChangeHtml(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
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
            $verificationIdentifier = $pathParams['verificationIdentifier'] ?? null;
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

    private function validateAndCallGetCreateUserPasswordHtml(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['user', 'create', '{verificationIdentifier}'],
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
            $verificationIdentifier = $pathParams['verificationIdentifier'] ?? null;
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
            return $this->getCreateUserPasswordHtml(
                $verificationIdentifier,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicHtmlControllerAbstract - Method: getCreateUserPasswordHtml()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetInviteRequestPage(Request $request): Response
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

    private function validateAndCallGetAlbumPage(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['album', '{albumSlug}'],
            $pathParts
        );
        $errors = [];

        $albumSlug = null;
        if (!isset($pathParams['albumSlug'])) {
            $errors[] = [
                'field' => 'albumSlug',
                'message' => 'required'
            ];
        } else {
            $albumSlug = $pathParams['albumSlug'] ?? null;
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
            return $this->getAlbumPage(
                $albumSlug,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicHtmlControllerAbstract - Method: getAlbumPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallLogEventClick(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['click', '{identifier}'],
            $pathParts
        );
        $errors = [];

        $identifier = null;
        if (!isset($pathParams['identifier'])) {
            $errors[] = [
                'field' => 'identifier',
                'message' => 'required'
            ];
        } else {
            $identifier = $pathParams['identifier'] ?? null;
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
            return $this->logEventClick(
                $identifier,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicHtmlControllerAbstract - Method: logEventClick()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetJsonFeed(Request $request): Response
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
            return $this->getJsonFeed(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicHtmlControllerAbstract - Method: getJsonFeed()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetRss(Request $request): Response
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
            return $this->getRss(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicHtmlControllerAbstract - Method: getRss()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetSitemap(Request $request): Response
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
            return $this->getSitemap(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in PublicHtmlControllerAbstract - Method: getSitemap()' .
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
            $pathParams = $this->pathParamsMatcher(
                ['user', 'create', '{verificationIdentifier}'],
                $pathParts,
                ['fixed', 'fixed', 'string']
            )
        ) {
            return $this->validateAndCallGetCreateUserPasswordHtml($request);
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

        if ($method === 'GET' &&
            $pathParams = $this->pathParamsMatcher(
                ['album', '{albumSlug}'],
                $pathParts,
                ['fixed', 'string']
            )
        ) {
            return $this->validateAndCallGetAlbumPage($request);
        }

        if ($method === 'GET' &&
            $pathParams = $this->pathParamsMatcher(
                ['click', '{identifier}'],
                $pathParts,
                ['fixed', 'string']
            )
        ) {
            return $this->validateAndCallLogEventClick($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['json-feed'],
                $pathParts,
                ['fixed']
            )
        ) {
            return $this->validateAndCallGetJsonFeed($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['rss'],
                $pathParts,
                ['fixed']
            )
        ) {
            return $this->validateAndCallGetRss($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['sitemap'],
                $pathParts,
                ['fixed']
            )
        ) {
            return $this->validateAndCallGetSitemap($request);
        }

        return null;
    }
}
