<?php

namespace Amora\Core\Router;

use Amora\App\Module\Form\Entity\PageContent;
use Amora\App\Value\AppPageContentType;
use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Entity\Response\HtmlResponseDataAnalytics;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Analytics\Service\AnalyticsService;
use Amora\Core\Module\Analytics\Value\CountDbColumn;
use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Module\Analytics\Value\Period;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Service\MediaService;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\Article\Value\PageContentType;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\AggregateBy;
use Amora\Core\Value\QueryOrderDirection;
use DateTimeImmutable;

final class BackofficeHtmlController extends BackofficeHtmlControllerAbstract
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ArticleService $articleService,
        private readonly MediaService $mediaService,
        private readonly AnalyticsService $analyticsService,
    ) {
        parent::__construct();
    }

    protected function authenticate(Request $request): bool
    {
        $session = $request->session;
        if (empty($session) || !$session->isAuthenticated() || !$session->isAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Endpoint: /backoffice/php-info
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getPhpInfoPage(Request $request): Response
    {
        phpinfo();
        die;
    }

    /**
     * Endpoint: /backoffice/dashboard
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getAdminDashboard(Request $request): Response
    {
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);

        return Response::createHtmlResponse(
            template: 'core/backoffice/dashboard',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdministrator'),
            ),
        );
    }

    /**
     * Endpoint: /backoffice/users
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getUsersAdminPage(Request $request): Response
    {
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        $users = $this->userService->filterUsersBy();
        return Response::createHtmlResponse(
            template: 'core/backoffice/users',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminUsers'),
                users: $users,
            ),
        );
    }

    /**
     * Endpoint: /backoffice/users/new
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getNewUserPage(Request $request): Response
    {
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);

        return Response::createHtmlResponse(
            template: 'core/backoffice/users-edit',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalNew') . ' ' .
                    $localisationUtil->getValue('globalUser')
            ),
        );
    }

    /**
     * Endpoint: /backoffice/users/{userId}/new
     * Method: GET
     *
     * @param int $userId
     * @param Request $request
     * @return Response
     */
    protected function getEditUserPage(int $userId, Request $request): Response
    {
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        $user = $this->userService->getUserForId($userId, true);
        if (empty($user)) {
            return Response::createNotFoundResponse($request);
        }

        return Response::createHtmlResponse(
            template: 'core/backoffice/users-edit',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalEdit') . ' ' .
                    $localisationUtil->getValue('globalUser'),
                user: $user,
            ),
        );
    }

    /**
     * Endpoint: /backoffice/articles
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getArticlesPage(Request $request): Response
    {
        $typeIdParam = $request->getGetParam('atId');
        $articleType = $typeIdParam && ArticleType::tryFrom($typeIdParam)
            ? ArticleType::from($typeIdParam)
            : null;

        $statusIdParam = $request->getGetParam('status');
        $articleStatus = $statusIdParam && ArticleStatus::tryFrom($statusIdParam)
            ? ArticleStatus::from($statusIdParam)
            : null;

        $languageIsoCodeParam = $request->getGetParam('lang');
        $articleLanguage = $languageIsoCodeParam && Language::tryFrom($languageIsoCodeParam)
            ? Language::from($languageIsoCodeParam)
            : null;

        $pagination = new Response\Pagination(itemsPerPage: 25);
        $articles = $this->articleService->filterArticlesBy(
            languageIsoCodes: $articleLanguage ? [$articleLanguage->value] : [],
            statusIds: $articleStatus ? [$articleStatus->value] : [],
            typeIds: $articleType ? [$articleType->value] : [],
            includeTags: true,
            includePublishedAtInTheFuture: true,
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy(field: 'updated_at', direction: QueryOrderDirection::DESC)],
                pagination: $pagination,
            )
        );
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);

        return Response::createHtmlResponse(
            template: 'core/backoffice/articles',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminArticles'),
                articles: $articles,
                pagination: $pagination,
            ),
        );
    }

    /**
     * Endpoint: /backoffice/articles/new
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getNewArticlePage(Request $request): Response
    {
        $articleTypeParam = $request->getGetParam('atId');
        $articleType = $articleTypeParam && ArticleType::tryFrom($articleTypeParam)
            ? ArticleType::from($articleTypeParam)
            : null;

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/articles-edit',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalNew') . ' ' .
                    $localisationUtil->getValue('globalArticle')
            ),
        );
    }

    /**
     * Endpoint: /backoffice/articles/{articleId}
     * Method: GET
     *
     * @param int $articleId
     * @param Request $request
     * @return Response
     */
    protected function getEditArticlePage(int $articleId, Request $request): Response
    {
        $article = $this->articleService->getArticleForId($articleId, true);
        if (empty($article)) {
            return Response::createNotFoundResponse($request);
        }

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        $articleSections = $this->articleService->getSectionsForArticleId($articleId);
        return Response::createHtmlResponse(
            template: 'core/backoffice/articles-edit',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalEdit') . ' ' .
                    $localisationUtil->getValue('globalArticle'),
                article: $article,
                articleSections: $articleSections,
            ),
        );
    }

    /**
     * Endpoint: /backoffice/images
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getImagesPage(Request $request): Response
    {
        $images = $this->mediaService->filterMediaBy(
            typeIds: [MediaType::Image->value],
            statusIds: [MediaStatus::Active->value],
            queryOptions: new QueryOptions(
                pagination: new Response\Pagination(itemsPerPage: 50),
            ),
        );

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/images',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminImages'),
                files: $images
            ),
        );
    }

    /**
     * Endpoint: /backoffice/media
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getMediaPage(Request $request): Response
    {
        $files = $this->mediaService->filterMediaBy(
            typeIds: [MediaType::PDF->value, MediaType::Unknown->value],
            statusIds: [MediaStatus::Active->value],
            queryOptions: new QueryOptions(
                pagination: new Response\Pagination(itemsPerPage: 50),
            ),
        );

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/media',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminMedia'),
                files: $files
            ),
        );
    }

    /**
     * Endpoint: /backoffice/analytics
     * Method: GET
     *
     * @param string|null $period
     * @param string|null $date
     * @param int|null $eventTypeId
     * @param Request $request
     * @return Response
     */
    protected function getAnalyticsPage(
        ?string $period,
        ?string $date,
        ?int $eventTypeId,
        Request $request,
    ): Response {
        $period = $period && Period::tryFrom($period) ? Period::from($period) : Period::Day;
        if (!$date || !DateUtil::isValidDateISO8601($date . 'T00:00:00Z')) {
            $now = new DateTimeImmutable();
            $date = $now->format('Y-m-d');
        }

        $from = Period::getFrom($period, $date);
        $to = Period::getTo($period, $from);

        $eventType = $eventTypeId && EventType::tryFrom($eventTypeId)
            ? EventType::from($eventTypeId)
            : EventType::Visitor;

        $report = $this->analyticsService->filterPageViewsBy(
            from: $from,
            to: $to,
            period: $period,
            eventType: $eventType,
        );

        $pages = $this->analyticsService->countTop(
            columnName: CountDbColumn::Page,
            from: $from,
            to: $to,
            eventType: $eventType,
        );

        $countries = $this->analyticsService->countTop(
            columnName: CountDbColumn::Country,
            from: $from,
            to: $to,
            eventType: $eventType,
        );

        $sources = $this->analyticsService->countTop(
            columnName: CountDbColumn::Referrer,
            from: $from,
            to: $to,
            eventType: $eventType,
        );

        $devices = $this->analyticsService->countTop(
            columnName: CountDbColumn::Device,
            from: $from,
            to: $to,
            eventType: $eventType,
        );

        $browsers = $this->analyticsService->countTop(
            columnName: CountDbColumn::Browser,
            from: $from,
            to: $to,
            eventType: $eventType,
        );

        $languages = $this->analyticsService->countTop(
            columnName: CountDbColumn::Language,
            from: $from,
            to: $to,
            eventType: $eventType,
        );

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/analytics',
            responseData: new HtmlResponseDataAnalytics(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminAnalytics'),
                reportPageViews: $report,
                pages: $pages,
                countries: $countries,
                sources: $sources,
                devices: $devices,
                browsers: $browsers,
                languages: $languages,
            ),
        );
    }


    /**
     * Endpoint: /backoffice/content
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getBackofficeContentList(Request $request): Response
    {
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);

        return Response::createHtmlResponse(
            template: 'app/backoffice/page-content-list',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('pageContentEditTitle'),
            ),
        );
    }

    /**
     * Endpoint: /backoffice/content/{id}
     * Method: GET
     *
     * @param int $id
     * @param Request $request
     * @return Response
     */
    protected function getBackofficeContentEdit(int $id, Request $request): Response
    {
        $pageContent = $this->articleService->getPageContentForId($id);

        if (!$pageContent) {
            return Response::createNotFoundResponse($request);
        }

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/page-content-edit',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('pageContentEditTitle' . $pageContent->type->name),
                pageContent: $pageContent,
            ),
        );
    }

    /**
     * Endpoint: /backoffice/content-type/{typeId}
     * Method: GET
     *
     * @param int $typeId
     * @param string $languageIsoCode
     * @param Request $request
     * @return Response
     */
    protected function getBackofficeContentForTypeEdit(
        int $typeId,
        string $languageIsoCode,
        Request $request
    ): Response {
        if (!AppPageContentType::tryFrom($typeId) && !PageContentType::tryFrom($typeId)) {
            return Response::createNotFoundResponse($request);
        }

        if (!Language::tryFrom($languageIsoCode)) {
            return Response::createNotFoundResponse($request);
        }

        $type = AppPageContentType::tryFrom($typeId)
            ? AppPageContentType::from($typeId)
            : PageContentType::from($typeId);

        $language = Language::from($languageIsoCode);
        $pageContentRes = $this->articleService->filterPageContentBy(
            languageIsoCodes: [$language->value],
            typeIds: [$type->value],
        );

        $pageContent = $pageContentRes[0]
            ?? $this->articleService->storePageContent(
                PageContent::getEmpty(
                    user: $request->session->user,
                    language: $language,
                    type: $type,
                ),
            );

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/page-content-edit',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('pageContentEditTitle' . $pageContent->type->name),
                pageContent: $pageContent,
            ),
        );
    }
}
