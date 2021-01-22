<?php

namespace uve\router;

use Exception;
use Throwable;
use uve\core\Core;
use uve\core\model\Request;
use uve\core\model\Response;
use uve\core\model\response\HtmlResponseData;
use uve\core\module\article\ArticleCore;
use uve\core\module\article\value\ArticleStatus;
use uve\core\util\StringUtil;

class Router
{
    public function handleRequest(Request $request): void
    {
        try {
            $response = $this->route($request);
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Error handling route' .
                ' - Error: ' . $t->getMessage() .
                ' - Trace: ' . $t->getTraceAsString()
            );
            $response = Response::createErrorResponse();
        }

        foreach ($response->getHeaders() as $header) {
            header($header);
        }

        echo $response->getOutput();
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    private function route(Request $request): Response
    {
        $path = $request->getPath();

        $arrayPath = explode('/', $path);
        $action = empty($arrayPath[0]) ? '' : $arrayPath[0];

        ///////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////
        //// Public actions

        $publicHtmlControllerActions = array(
            'a' => true,
            'e' => true,
            'home' => true,
            'login' => true,
            'register' => true,
            'invite-request' => true,
            'user' => true
        );

        $publicApiControllerActions = [
            'papi' => true
        ];

        $authorisedHtmlControllerActions = [
            'logout' => true,
            'dashboard' => true,
            'invitation' => true,
            'guests' => true,
            'account' => true,
        ];

        $authorisedApiControllerActions = [
            'api' => true
        ];

        $backofficeHtmlControllerActions = [
            'backoffice' => true,
        ];

        $backofficeApiControllerActions = [
            'back' => true,
        ];

        ///////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////
        //// Authenticate actions

        if (isset($backofficeHtmlControllerActions[$action])) {
            return RouterCore::getBackofficeHtmlController()->route($request);
        }

        if (isset($backofficeApiControllerActions[$action])) {
            return RouterCore::getBackofficeApiController()->route($request);
        }

        if (isset($authorisedHtmlControllerActions[$action])) {
            return RouterCore::getAuthorisedHtmlController()->route($request);
        }

        if (isset($authorisedApiControllerActions[$action])) {
            return RouterCore::getAuthorisedApiController()->route($request);
        }

        if (isset($publicApiControllerActions[$action])) {
            return RouterCore::getPublicApiController()->route($request);
        }

        if (isset($publicHtmlControllerActions[$action])) {
            return RouterCore::getPublicHtmlController()->route($request);
        }

        return $this->getArticlePage($request->getPath(), $request);
    }

    private function getArticlePage(string $articleUri, Request $request): Response
    {
        $article = ArticleCore::getArticleService()->getArticleForUri($articleUri);
        if (empty($article)) {
            return Response::createFrontendPublicHtmlResponse(
                'shared/404',
                new HtmlResponseData($request)
            );
        }

        $preview = $request->getGetParam('preview');
        if ($article->getStatusId() !== ArticleStatus::PUBLISHED
            && (!$request->getSession()
                || !$request->getSession()->isAdmin()
                || !StringUtil::isTrue($preview)
            )
        ) {
            return Response::createFrontendPublicHtmlResponse(
                'shared/404',
                new HtmlResponseData($request)
            );
        }

        return Response::createFrontendPublicHtmlResponse(
            'shared/home-article',
            new HtmlResponseData(
                $request,
                $article->getTitle(),
                $article->getContentExcerpt(),
                $article->getMainImageSrc(),
                [$article],
            )
        );
    }
}
