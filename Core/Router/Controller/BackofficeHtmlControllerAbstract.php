<?php

namespace Amora\Core\Router;

use Amora\Core\Core;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Router\AbstractController;
use Amora\Core\Router\RouterCore;
use Amora\Core\Util\StringUtil;
use Throwable;

abstract class BackofficeHtmlControllerAbstract extends AbstractController
{
    public function __construct()
    {

    }

    abstract protected function authenticate(Request $request): bool;

    /**
     * Endpoint: /backoffice/php-info
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getPhpInfoPage(Request $request): Response;

    /**
     * Endpoint: /backoffice/dashboard
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getAdminDashboard(Request $request): Response;

    /**
     * Endpoint: /backoffice/users
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getUsersAdminPage(Request $request): Response;

    /**
     * Endpoint: /backoffice/users/new
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getNewUserPage(Request $request): Response;

    /**
     * Endpoint: /backoffice/users/{userId}
     * Method: GET
     *
     * @param int $userId
     * @param Request $request
     * @return Response
     */
    abstract protected function getEditUserPage(int $userId, Request $request): Response;

    /**
     * Endpoint: /backoffice/articles
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getArticlesPage(Request $request): Response;

    /**
     * Endpoint: /backoffice/articles/new
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getNewArticlePage(Request $request): Response;

    /**
     * Endpoint: /backoffice/articles/{articleId}
     * Method: GET
     *
     * @param int $articleId
     * @param Request $request
     * @return Response
     */
    abstract protected function getEditArticlePage(int $articleId, Request $request): Response;

    /**
     * Endpoint: /backoffice/articles/{articleId}/preview
     * Method: GET
     *
     * @param int $articleId
     * @param Request $request
     * @return Response
     */
    abstract protected function getArticlePreviewPage(int $articleId, Request $request): Response;

    /**
     * Endpoint: /backoffice/albums
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getAlbumsPage(Request $request): Response;

    /**
     * Endpoint: /backoffice/albums/{albumId}
     * Method: GET
     *
     * @param int $albumId
     * @param Request $request
     * @return Response
     */
    abstract protected function getViewAlbumPage(int $albumId, Request $request): Response;

    /**
     * Endpoint: /backoffice/albums/{albumId}/edit
     * Method: GET
     *
     * @param int $albumId
     * @param Request $request
     * @return Response
     */
    abstract protected function getEditAlbumPage(int $albumId, Request $request): Response;

    /**
     * Endpoint: /backoffice/albums/new
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getNewAlbumPage(Request $request): Response;

    /**
     * Endpoint: /backoffice/images
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getImagesPage(Request $request): Response;

    /**
     * Endpoint: /backoffice/media
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getMediaPage(Request $request): Response;

    /**
     * Endpoint: /backoffice/analytics
     * Method: GET
     *
     * @param string|null $period
     * @param string|null $date
     * @param int|null $eventTypeId
     * @param string|null $url
     * @param string|null $device
     * @param string|null $browser
     * @param string|null $countryIsoCode
     * @param string|null $languageIsoCode
     * @param int|null $itemsCount
     * @param Request $request
     * @return Response
     */
    abstract protected function getAnalyticsPage(
        ?string $period,
        ?string $date,
        ?int $eventTypeId,
        ?string $url,
        ?string $device,
        ?string $browser,
        ?string $countryIsoCode,
        ?string $languageIsoCode,
        ?int $itemsCount,
        Request $request
    ): Response;

    /**
     * Endpoint: /backoffice/content
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getBackofficeContentList(Request $request): Response;

    /**
     * Endpoint: /backoffice/content-type/{typeId}/language/{languageIsoCode}
     * Method: GET
     *
     * @param int $typeId
     * @param string $languageIsoCode
     * @param Request $request
     * @return Response
     */
    abstract protected function getBackofficeContentForTypeEdit(
        int $typeId,
        string $languageIsoCode,
        Request $request
    ): Response;

    /**
     * Endpoint: /backoffice/emails
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    abstract protected function getEmailsAdminPage(Request $request): Response;

    private function validateAndCallGetPhpInfoPage(Request $request): Response
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
            return $this->getPhpInfoPage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getPhpInfoPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetAdminDashboard(Request $request): Response
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
            return $this->getAdminDashboard(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getAdminDashboard()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetUsersAdminPage(Request $request): Response
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
            return $this->getUsersAdminPage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getUsersAdminPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetNewUserPage(Request $request): Response
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
            return $this->getNewUserPage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getNewUserPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetEditUserPage(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['backoffice', 'users', '{userId}'],
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
            return $this->getEditUserPage(
                $userId,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getEditUserPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetArticlesPage(Request $request): Response
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
            return $this->getArticlesPage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getArticlesPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetNewArticlePage(Request $request): Response
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
            return $this->getNewArticlePage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getNewArticlePage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetEditArticlePage(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['backoffice', 'articles', '{articleId}'],
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
            return $this->getEditArticlePage(
                $articleId,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getEditArticlePage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetArticlePreviewPage(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['backoffice', 'articles', '{articleId}', 'preview'],
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
            return $this->getArticlePreviewPage(
                $articleId,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getArticlePreviewPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetAlbumsPage(Request $request): Response
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
            return $this->getAlbumsPage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getAlbumsPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetViewAlbumPage(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['backoffice', 'albums', '{albumId}'],
            $pathParts
        );
        $errors = [];

        $albumId = null;
        if (!isset($pathParams['albumId'])) {
            $errors[] = [
                'field' => 'albumId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['albumId'])) {
                $errors[] = [
                    'field' => 'albumId',
                    'message' => 'must be an integer'
                ];
            } else {
                $albumId = intval($pathParams['albumId']);
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
            return $this->getViewAlbumPage(
                $albumId,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getViewAlbumPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetEditAlbumPage(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['backoffice', 'albums', '{albumId}', 'edit'],
            $pathParts
        );
        $errors = [];

        $albumId = null;
        if (!isset($pathParams['albumId'])) {
            $errors[] = [
                'field' => 'albumId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['albumId'])) {
                $errors[] = [
                    'field' => 'albumId',
                    'message' => 'must be an integer'
                ];
            } else {
                $albumId = intval($pathParams['albumId']);
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
            return $this->getEditAlbumPage(
                $albumId,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getEditAlbumPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetNewAlbumPage(Request $request): Response
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
            return $this->getNewAlbumPage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getNewAlbumPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetImagesPage(Request $request): Response
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
            return $this->getImagesPage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getImagesPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetMediaPage(Request $request): Response
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
            return $this->getMediaPage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getMediaPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetAnalyticsPage(Request $request): Response
    {
        $queryParams = $request->getParams;
        $errors = [];


        $period = $queryParams['period'] ?? null;

        $date = $queryParams['date'] ?? null;

        if (isset($queryParams['eventTypeId']) && strlen($queryParams['eventTypeId']) > 0) {
            $eventTypeId = intval($queryParams['eventTypeId']);
        } else {
            $eventTypeId = null;
        }

        $url = $queryParams['url'] ?? null;

        $device = $queryParams['device'] ?? null;

        $browser = $queryParams['browser'] ?? null;

        $countryIsoCode = $queryParams['countryIsoCode'] ?? null;

        $languageIsoCode = $queryParams['languageIsoCode'] ?? null;

        if (isset($queryParams['itemsCount']) && strlen($queryParams['itemsCount']) > 0) {
            $itemsCount = intval($queryParams['itemsCount']);
        } else {
            $itemsCount = null;
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
            return $this->getAnalyticsPage(
                $period,
                $date,
                $eventTypeId,
                $url,
                $device,
                $browser,
                $countryIsoCode,
                $languageIsoCode,
                $itemsCount,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getAnalyticsPage()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetBackofficeContentList(Request $request): Response
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
            return $this->getBackofficeContentList(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getBackofficeContentList()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetBackofficeContentForTypeEdit(Request $request): Response
    {
        $pathParts = $request->pathWithoutLanguage;
        $pathParams = $this->getPathParams(
            ['backoffice', 'content-type', '{typeId}', 'language', '{languageIsoCode}'],
            $pathParts
        );
        $errors = [];

        $typeId = null;
        if (!isset($pathParams['typeId'])) {
            $errors[] = [
                'field' => 'typeId',
                'message' => 'required'
            ];
        } else {
            if (!is_numeric($pathParams['typeId'])) {
                $errors[] = [
                    'field' => 'typeId',
                    'message' => 'must be an integer'
                ];
            } else {
                $typeId = intval($pathParams['typeId']);
            }
        }

        $languageIsoCode = null;
        if (!isset($pathParams['languageIsoCode'])) {
            $errors[] = [
                'field' => 'languageIsoCode',
                'message' => 'required'
            ];
        } else {
            $languageIsoCode = $pathParams['languageIsoCode'] ?? null;
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
            return $this->getBackofficeContentForTypeEdit(
                $typeId,
                $languageIsoCode,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getBackofficeContentForTypeEdit()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }

    private function validateAndCallGetEmailsAdminPage(Request $request): Response
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
            return $this->getEmailsAdminPage(
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in BackofficeHtmlControllerAbstract - Method: getEmailsAdminPage()' .
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
                ['backoffice', 'php-info'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetPhpInfoPage($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['backoffice', 'dashboard'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetAdminDashboard($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['backoffice', 'users'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetUsersAdminPage($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['backoffice', 'users', 'new'],
                $pathParts,
                ['fixed', 'fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetNewUserPage($request);
        }

        if ($method === 'GET' &&
            $pathParams = $this->pathParamsMatcher(
                ['backoffice', 'users', '{userId}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallGetEditUserPage($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['backoffice', 'articles'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetArticlesPage($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['backoffice', 'articles', 'new'],
                $pathParts,
                ['fixed', 'fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetNewArticlePage($request);
        }

        if ($method === 'GET' &&
            $pathParams = $this->pathParamsMatcher(
                ['backoffice', 'articles', '{articleId}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallGetEditArticlePage($request);
        }

        if ($method === 'GET' &&
            $pathParams = $this->pathParamsMatcher(
                ['backoffice', 'articles', '{articleId}', 'preview'],
                $pathParts,
                ['fixed', 'fixed', 'int', 'fixed']
            )
        ) {
            return $this->validateAndCallGetArticlePreviewPage($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['backoffice', 'albums'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetAlbumsPage($request);
        }

        if ($method === 'GET' &&
            $pathParams = $this->pathParamsMatcher(
                ['backoffice', 'albums', '{albumId}'],
                $pathParts,
                ['fixed', 'fixed', 'int']
            )
        ) {
            return $this->validateAndCallGetViewAlbumPage($request);
        }

        if ($method === 'GET' &&
            $pathParams = $this->pathParamsMatcher(
                ['backoffice', 'albums', '{albumId}', 'edit'],
                $pathParts,
                ['fixed', 'fixed', 'int', 'fixed']
            )
        ) {
            return $this->validateAndCallGetEditAlbumPage($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['backoffice', 'albums', 'new'],
                $pathParts,
                ['fixed', 'fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetNewAlbumPage($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['backoffice', 'images'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetImagesPage($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['backoffice', 'media'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetMediaPage($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['backoffice', 'analytics'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetAnalyticsPage($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['backoffice', 'content'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetBackofficeContentList($request);
        }

        if ($method === 'GET' &&
            $pathParams = $this->pathParamsMatcher(
                ['backoffice', 'content-type', '{typeId}', 'language', '{languageIsoCode}'],
                $pathParts,
                ['fixed', 'fixed', 'int', 'fixed', 'string']
            )
        ) {
            return $this->validateAndCallGetBackofficeContentForTypeEdit($request);
        }

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['backoffice', 'emails'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetEmailsAdminPage($request);
        }

        return null;
    }
}
