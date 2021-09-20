<?php

namespace Amora\Router;

use Amora\Core\Module\Action\Service\ActionService;
use Exception;
use Throwable;
use Amora\Core\Core;
use Amora\Core\Model\Request;
use Amora\Core\Model\Response;
use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Util\StringUtil;

class Router
{
    public function __construct(
        private ActionService $actionService,
    ) {}

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
            'rss' => 'true',
            'invite-request' => true,
            'user' => true
        );

        $publicApiControllerActions = [
            'papi' => true
        ];

        $authorisedHtmlControllerActions = [
            'logout' => true,
            'dashboard' => true,
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
        $this->actionService->logAction($request, $request->getSession());

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

        $img = $article->getMainImageId()
            ? ArticleCore::getImageService()->getImageForId($article->getMainImageId())
            : null;
        $siteImageUrl = $img
            ? rtrim(Core::getConfigValue('baseUrl'), ' /') . $img->getFullUrlMedium()
            : null;

        return Response::createFrontendPublicHtmlResponse(
            'shared/home-article',
            new HtmlResponseData(
                $request,
                $article->getTitle(),
                $article->getContentExcerpt(),
                $siteImageUrl,
                [$article],
            )
        );
    }
}
