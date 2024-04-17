<?php

namespace Amora\App\Router;

use Amora\Core\Core;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Router\AbstractController;
use Amora\Core\Router\RouterCore;
use Amora\Core\Util\StringUtil;
use Throwable;

abstract class AppPublicApiControllerAbstract extends AbstractController
{
    public function __construct()
    {
        require_once Core::getPathRoot() . '/App/Router/Controller/Response/AppPublicApiControllerGetSearchResultsSuccessResponse.php';
    }

    abstract protected function authenticate(Request $request): bool;

    /**
     * Endpoint: /papi/search
     * Method: GET
     *
     * @param string $q
     * @param string|null $isPublic
     * @param int|null $searchTypeId
     * @param Request $request
     * @return Response
     */
    abstract protected function getSearchResults(
        string $q,
        ?string $isPublic,
        ?int $searchTypeId,
        Request $request
    ): Response;

    private function validateAndCallGetSearchResults(Request $request): Response
    {
        $queryParams = $request->getParams;
        $errors = [];

        $q = null;
        if (!isset($queryParams['q'])) {
            $errors[] = [
                'field' => 'q',
                'message' => 'required'
            ];
        } else {
            $q = $queryParams['q'] ?? null;
        }


        $isPublic = $queryParams['isPublic'] ?? null;

        if (isset($queryParams['searchTypeId']) && strlen($queryParams['searchTypeId']) > 0) {
            $searchTypeId = intval($queryParams['searchTypeId']);
        } else {
            $searchTypeId = null;
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
            return $this->getSearchResults(
                $q,
                $isPublic,
                $searchTypeId,
                $request
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in AppPublicApiControllerAbstract - Method: getSearchResults()' .
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

        $pathParts = $request->pathWithoutLanguage;
        $method = $request->method;

        if ($method === 'GET' &&
            $this->pathParamsMatcher(
                ['papi', 'search'],
                $pathParts,
                ['fixed', 'fixed']
            )
        ) {
            return $this->validateAndCallGetSearchResults($request);
        }

        return null;
    }
}
