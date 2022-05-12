<?php

namespace Amora\Core\Router;

use Amora\App\Router\AppRouter;
use Amora\App\Router\AppRouterCore;
use Amora\Core\Module\Action\Service\ActionService;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Value\ArticleType;
use Exception;
use Throwable;
use Amora\Core\Core;
use Amora\Core\Model\Request;
use Amora\Core\Model\Response;
use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\Article\Value\ArticleStatus;

class Router
{
    private const PUBLIC_HTML_CONTROLLER_ACTIONS = [
        'home' => true,
        'invite-request' => true,
        'login' => true,
        'register' => true,
        'rss' => true,
        'sitemap' => true,
        'user' => true,
    ];

    private const PUBLIC_API_CONTROLLER_ACTIONS = [
        'papi' => true
    ];

    private const AUTHORISED_HTML_CONTROLLER_ACTIONS = [
        'logout' => true,
        'dashboard' => true,
        'account' => true,
    ];

    private const AUTHORISED_API_CONTROLLER_ACTIONS = [
        'api' => true
    ];

    private const BACKOFFICE_HTML_CONTROLLER_ACTIONS = [
        'backoffice' => true,
    ];

    private const BACKOFFICE_API_CONTROLLER_ACTIONS = [
        'back' => true,
    ];

    public function __construct(
        private ActionService $actionService,
    ) {}

    public static function getReservedPaths(): array
    {
        return array_merge(
            array_keys(self::PUBLIC_HTML_CONTROLLER_ACTIONS),
            array_keys(self::PUBLIC_API_CONTROLLER_ACTIONS),
            array_keys(self::AUTHORISED_HTML_CONTROLLER_ACTIONS),
            array_keys(self::AUTHORISED_API_CONTROLLER_ACTIONS),
            array_keys(self::BACKOFFICE_HTML_CONTROLLER_ACTIONS),
            array_keys(self::BACKOFFICE_API_CONTROLLER_ACTIONS),
        );
    }

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

        echo $response->output;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    private function route(Request $request): Response
    {
        $this->actionService->logAction($request);

        $path = $request->getPath();

        $arrayPath = explode('/', $path);
        $action = empty($arrayPath[0]) ? '' : $arrayPath[0];

        $publicHtmlControllerActions = array_merge(
            self::PUBLIC_HTML_CONTROLLER_ACTIONS,
            AppRouter::PUBLIC_HTML_CONTROLLER_ACTIONS,
        );

        $publicApiControllerActions = array_merge(
            self::PUBLIC_API_CONTROLLER_ACTIONS,
            AppRouter::PUBLIC_API_CONTROLLER_ACTIONS,
        );

        $authorisedHtmlControllerActions = array_merge(
            self::AUTHORISED_HTML_CONTROLLER_ACTIONS,
            AppRouter::AUTHORISED_HTML_CONTROLLER_ACTIONS,
        );

        $authorisedApiControllerActions = array_merge(
            self::AUTHORISED_API_CONTROLLER_ACTIONS,
            AppRouter::AUTHORISED_API_CONTROLLER_ACTIONS,
        );

        $backofficeHtmlControllerActions = array_merge(
            self::BACKOFFICE_HTML_CONTROLLER_ACTIONS,
            AppRouter::BACKOFFICE_HTML_CONTROLLER_ACTIONS,
        );

        $backofficeApiControllerActions = array_merge(
            self::BACKOFFICE_API_CONTROLLER_ACTIONS,
            AppRouter::BACKOFFICE_API_CONTROLLER_ACTIONS,
        );

        if (isset($backofficeHtmlControllerActions[$action])) {
            $res = AppRouterCore::getAppBackofficeHtmlController()->route($request)
                ?? RouterCore::getBackofficeHtmlController()->route($request);

            return $res ?: Response::createNotFoundResponse();
        }

        if (isset($backofficeApiControllerActions[$action])) {
            $res = AppRouterCore::getAppBackofficeApiController()->route($request)
                ?? RouterCore::getBackofficeApiController()->route($request);

            return $res ?: Response::createNotFoundResponse();
        }

        if (isset($authorisedHtmlControllerActions[$action])) {
            $res = AppRouterCore::getAppAuthorisedHtmlController()->route($request)
                ?? RouterCore::getAuthorisedHtmlController()->route($request);

            return $res ?: Response::createNotFoundResponse();
        }

        if (isset($authorisedApiControllerActions[$action])) {
            $res = AppRouterCore::getAppAuthorisedApiController()->route($request)
                ?? RouterCore::getAuthorisedApiController()->route($request);

            return $res ?: Response::createNotFoundResponse();
        }

        if (isset($publicApiControllerActions[$action])) {
            $res = AppRouterCore::getAppPublicApiController()->route($request)
                ?? RouterCore::getPublicApiController()->route($request);

            return $res ?: Response::createNotFoundResponse();
        }

        if (isset($publicHtmlControllerActions[$action])) {
            $res = AppRouterCore::getAppPublicHtmlController()->route($request)
                ?? RouterCore::getPublicHtmlController()->route($request);

            return $res ?: Response::createNotFoundResponse();
        }

        $res = AppRouter::route($request);
        if ($res) {
            return $res;
        }

        return $this->getArticlePage($request->getPath(), $request);
    }

    private function getArticlePage(string $articleUri, Request $request): Response
    {
        $articleService = ArticleCore::getArticleService();
        $article = $articleService->getArticleForUri(
            uri: $articleUri,
            includePublishedAtInTheFuture: false,
        );

        if (empty($article)) {
            return Response::createHtmlResponse(
                template: 'core/frontend/public/404',
                responseData: new HtmlResponseData($request),
            );
        }

        if (!$this->displayArticle($article, $request->session?->isAdmin())) {
            return Response::createHtmlResponse(
                template: 'core/frontend/public/404',
                responseData: new HtmlResponseData($request),
            );
        }

        $img = $article->mainImageId
            ? ArticleCore::getImageService()->getImageForId($article->mainImageId)
            : null;
        $siteImageUrl = $img
            ? rtrim(Core::getConfig()->baseUrl, ' /') . $img->getFullUrlMedium()
            : null;
        $isAdmin = $request->session && $request->session->isAdmin();

        return Response::createHtmlResponse(
            template: 'core/frontend/public/home-article',
            responseData: new HtmlResponseData(
                request: $request,
                pageTitle: $article->title,
                pageDescription: $article->getContentExcerpt(),
                mainImageSiteUri: $siteImageUrl,
                articles: [$article],
                previousBlogPost: $article->publishOn
                    ? $articleService->getPreviousBlogPost(
                        publishedBefore: $article->publishOn,
                        isAdmin: $isAdmin,
                    ) : null,
                nextBlogPost: $article->publishOn
                    ? $articleService->getNextBlogPost(
                        publishedAfter: $article->publishOn,
                        isAdmin: $isAdmin,
                    ) : null,
                postBottomContent: $articleService->getArticlePartialContent(
                    articleType: ArticleType::PartialContentBlogBottom,
                    language: $request->siteLanguage,
                ),
            ),
        );
    }

    private function displayArticle(Article $article, ?bool $isAdmin): bool {
        if ($isAdmin) {
            return true;
        }

        if ($article->status === ArticleStatus::Published) {
            return true;
        }

        return false;
    }
}
