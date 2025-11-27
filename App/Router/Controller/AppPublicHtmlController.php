<?php

namespace Amora\App\Router;

use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Entity\Response\Feedback;
use Amora\App\Entity\AppHtmlHomepageResponseData;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\Article\Value\PageContentType;
use Amora\Core\Module\User\Service\SessionService;
use Amora\Core\Value\QueryOrderDirection;
use Throwable;

final readonly class AppPublicHtmlController extends AppPublicHtmlControllerAbstract
{
    public function __construct(
        private SessionService $sessionService,
        private ArticleService $articleService,
    ) {
        parent::__construct();
    }

    protected function authenticate(Request $request): bool
    {
        try {
            if ($request->session) {
                $this->sessionService->updateSessionExpiryDateAndValidUntil(
                    sid: $request->session->sessionId,
                    sessionId: $request->session->id,
                );
            }
        } catch (Throwable) {
        }

        return true;
    }

    public function buildHomepageResponse(
        Request $request,
        ?Feedback $feedback = null,
    ): Response {
        $isAdmin = $request->session && $request->session->isAdmin();
        $statusIds = $isAdmin
            ? [
                ArticleStatus::Published->value,
                ArticleStatus::Unlisted->value,
                ArticleStatus::Private->value,
            ]
            : [ArticleStatus::Published->value];
        $pagination = new Response\Pagination(itemsPerPage: 15);
        $blogArticles = $this->articleService->filterArticleBy(
            statusIds: $statusIds,
            typeIds: [ArticleType::Blog->value],
            queryOptions: new QueryOptions(
                orderBy: [
                    new QueryOrderBy(
                        field: "published_at",
                        direction: QueryOrderDirection::DESC,
                    ),
                ],
                pagination: $pagination,
            ),
        );

        $pageContent = $this->articleService->getPageContent(
            type: PageContentType::Homepage,
            language: $request->siteLanguage,
        );

        return Response::createHtmlResponse(
            template: "app/public/home",
            responseData: new AppHtmlHomepageResponseData(
                request: $request,
                pagination: $pagination,
                feedback: $feedback,
                isPublicPage: true,
                pageContent: $pageContent,
                homeArticles: [],
                blogArticles: $blogArticles,
            ),
        );
    }
}
