<?php

namespace Amora\Core\Router;

use Amora\App\Router\AppRouter;
use Amora\App\Router\AppRouterCore;
use Amora\App\Value\Language;
use Amora\Core\Module\Analytics\Service\AnalyticsService;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Value\PageContentType;
use Amora\Core\Util\UrlBuilderUtil;
use Exception;
use Throwable;
use Amora\Core\Core;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Entity\Response\HtmlResponseData;
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
        'json-feed' => true,
        'sitemap' => true,
        'user' => true,
        'album' => true,
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
        private readonly AnalyticsService $analyticsService,
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

    public static function getApiActions(): array
    {
        return array_merge(
            self::PUBLIC_API_CONTROLLER_ACTIONS,
            self::AUTHORISED_API_CONTROLLER_ACTIONS,
            self::BACKOFFICE_API_CONTROLLER_ACTIONS,
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
        if ($this->displayEmptyPage($request)) {
            return Response::createHtmlResponse(
                template: 'core/public/hidden',
                responseData: new HtmlResponseData(
                    request: $request,
                ),
            );
        }

        $this->analyticsService->logEvent($request);
        $action = $request->getAction();

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
            if (!Core::isValidBackofficeLanguage($request->siteLanguage)) {
                $arrayPath = explode('/', $request->path);
                if (!empty($arrayPath[0]) && strlen($arrayPath[0]) === 2) {
                    $uppercaseLanguage = strtoupper($arrayPath[0]);
                    if (Language::tryFrom($uppercaseLanguage)) {
                        array_shift($arrayPath);
                        return Response::createRedirectResponse(
                            url: '/' . strtolower(Core::getDefaultBackofficeLanguage()->value) . '/' . implode('/', $arrayPath),
                        );
                    } else {
                        return Response::createRedirectResponse(
                            url: UrlBuilderUtil::buildBackofficeDashboardUrl(Core::getDefaultBackofficeLanguage()),
                        );
                    }
                }

                return Response::createRedirectResponse(
                    url: '/' . strtolower(Core::getDefaultBackofficeLanguage()->value) . '/' . trim($request->path, ' /'),
                );
            }

            $res = AppRouterCore::getAppBackofficeHtmlController()->route($request)
                ?? RouterCore::getBackofficeHtmlController()->route($request);

            return $res ?: Response::createNotFoundResponse($request);
        }

        if (isset($backofficeApiControllerActions[$action])) {
            $res = AppRouterCore::getAppBackofficeApiController()->route($request)
                ?? RouterCore::getBackofficeApiController()->route($request);

            return $res ?: Response::createNotFoundResponse($request);
        }

        if (isset($authorisedHtmlControllerActions[$action])) {
            $res = AppRouterCore::getAppAuthorisedHtmlController()->route($request)
                ?? RouterCore::getAuthorisedHtmlController()->route($request);

            return $res ?: Response::createNotFoundResponse($request);
        }

        if (isset($authorisedApiControllerActions[$action])) {
            $res = AppRouterCore::getAppAuthorisedApiController()->route($request)
                ?? RouterCore::getAuthorisedApiController()->route($request);

            return $res ?: Response::createNotFoundResponse($request);
        }

        if (isset($publicApiControllerActions[$action])) {
            $res = AppRouterCore::getAppPublicApiController()->route($request)
                ?? RouterCore::getPublicApiController()->route($request);

            return $res ?: Response::createNotFoundResponse($request);
        }

        if (isset($publicHtmlControllerActions[$action])) {
            $res = AppRouterCore::getAppPublicHtmlController()->route($request)
                ?? RouterCore::getPublicHtmlController()->route($request);

            return $res ?: Response::createNotFoundResponse($request);
        }

        $res = AppRouter::route($request);
        if ($res) {
            return $res;
        }

        return $this->getArticlePage($request->getPathWithoutLanguageAsString(), $request);
    }

    private function getArticlePage(string $articlePath, Request $request): Response
    {
        $articleService = ArticleCore::getArticleService();
        $article = $articleService->getArticleForPath(
            path: $articlePath,
            includePublishedAtInTheFuture: false,
        );

        if (empty($article)) {
            return Response::createNotFoundResponse($request);
        }

        if ($articlePath !== $article->path) {
            return Response::createPermanentRedirectResponse(
                UrlBuilderUtil::buildPublicArticlePath($article->path, $request->siteLanguage),
            );
        }

        if (!$this->displayArticle($article, $request->session?->isAdmin())) {
            return Response::createNotFoundResponse($request);
        }

        $img = $article->mainImageId
            ? ArticleCore::getMediaService()->getMediaForId($article->mainImageId)
            : null;
        $siteImageUrl = $img?->getPathWithNameMedium();
        $isAdmin = $request->session && $request->session->isAdmin();
        $articleSections = $articleService->getSectionsForArticleId($article->id);

        return Response::createHtmlResponse(
            template: 'app/public/article-view',
            responseData: new HtmlResponseData(
                request: $request,
                pageTitle: $article->title,
                pageDescription: $article->getContentExcerpt(),
                siteImageUrl: $siteImageUrl,
                article: $article,
                articleSections: $articleSections,
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
                postBottomContent: $articleService->getPageContent(
                    type: PageContentType::BlogBottom,
                    language: $request->siteLanguage,
                ),
            ),
        );
    }

    private function displayArticle(Article $article, ?bool $isAdmin): bool
    {
        if ($isAdmin) {
            return true;
        }

        if ($article->status === ArticleStatus::Published || $article->status === ArticleStatus::Unlisted) {
            return true;
        }

        return false;
    }

    private function displayEmptyPage(Request $request): bool {
        $path = '/' . $request->path;

        if ($path === UrlBuilderUtil::PUBLIC_HTML_LOGIN ||
            $path === UrlBuilderUtil::PUBLIC_API_LOGIN
        ) {
            return false;
        }

        if ($request->session?->isAuthenticated()) {
            return false;
        }

        $token = Core::getConfig()->hiddenSiteToken;
        if (!$token) {
            return false;
        }

        if ($request->getGetParam('ht') === $token) {
            return false;
        }

        return true;
    }
}
