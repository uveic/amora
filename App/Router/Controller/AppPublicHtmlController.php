<?php

namespace Amora\App\Router;

use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Response\HtmlHomepageResponseData;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\Article\Value\PageContentType;
use Amora\Core\Value\QueryOrderDirection;

final class AppPublicHtmlController extends AppPublicHtmlControllerAbstract
{
    public function __construct(
        private readonly ArticleService $articleService,
    ) {
        parent::__construct();
    }

    protected function authenticate(Request $request): bool
    {
        return true;
    }

    public function buildHomepageResponse(
        Request $request,
        ?Feedback $feedback = null,
    ): Response {
        $isAdmin = $request->session && $request->session->isAdmin();
        $statusIds = $isAdmin
            ? [ArticleStatus::Published->value, ArticleStatus::Unlisted->value, ArticleStatus::Private->value]
            : [ArticleStatus::Published->value];
        $pagination = new Response\Pagination(itemsPerPage: 15);
        $blogArticles = $this->articleService->filterArticlesBy(
            statusIds: $statusIds,
            typeIds: [ArticleType::Blog->value],
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy(field: 'published_at', direction: QueryOrderDirection::DESC)],
                pagination: $pagination,
            ),
        );

        $pageContent = $this->articleService->getPageContent(
            type: PageContentType::Homepage,
            language: $request->siteLanguage,
        );

        return Response::createHtmlResponse(
            template: 'app/frontend/public/home',
            responseData: new HtmlHomepageResponseData(
                request: $request,
                pagination: $pagination,
                pageContent: $pageContent,
                homeArticles: [],
                blogArticles: $blogArticles,
                feedback: $feedback,
            ),
        );
    }
}
