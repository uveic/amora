<?php

namespace Amora\Core\Router;

use Amora\App\Module\Form\Entity\PageContent;
use Amora\App\Value\AppPageContentType;
use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Entity\Response\HtmlResponseDataAnalytics;
use Amora\Core\Entity\Util\DashboardCount;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Album\Service\AlbumService;
use Amora\Core\Module\Album\Value\AlbumStatus;
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
use Amora\Core\Module\Mailer\Service\MailerService;
use Amora\Core\Module\User\Service\SessionService;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Util\DateUtil;
use Amora\Core\Value\QueryOrderDirection;
use DateTimeImmutable;

final class BackofficeHtmlController extends BackofficeHtmlControllerAbstract
{
    public function __construct(
        private readonly SessionService $sessionService,
        private readonly UserService $userService,
        private readonly ArticleService $articleService,
        private readonly MediaService $mediaService,
        private readonly AlbumService $albumService,
        private readonly AnalyticsService $analyticsService,
        private readonly MailerService $mailerService,
    ) {
        parent::__construct();
    }

    protected function authenticate(Request $request): bool
    {
        $session = $request->session;
        if (empty($session) || !$session->isAuthenticated() || !$session->isAdmin()) {
            return false;
        }

        return $this->sessionService->refreshSession(
            sid: $request->session->sessionId,
            sessionId: $request->session->id,
        );
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

        $articlesCount = $this->articleService->getTotalArticles();
        $mediaCount = $this->mediaService->getTotalMedia();
        $userCount = $this->userService->getTotalUsers();
        $albumCount = $this->albumService->getTotalAlbums();

        $now = new DateTimeImmutable();
        $fromToday = DateUtil::convertStringToDateTimeImmutable($now->format('Y-m-d 00:00:00'));
        $toToday = DateUtil::convertStringToDateTimeImmutable($now->format('Y-m-d 23:59:59'));

        $visitorsToday = $this->analyticsService->countTop(
            columnName: CountDbColumn::Visitor,
            from: $fromToday,
            to: $toToday,
            eventType: EventType::Visitor,
            limit: 1000000,
        );

        $reportPageViewsToday = $this->analyticsService->countPageViews(
            from: $fromToday,
            to: $toToday,
            period: Period::Day,
            eventType: EventType::Visitor,
        );

        return Response::createHtmlResponse(
            template: 'app/backoffice/dashboard',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdministrator'),
                dashboardCount: new DashboardCount(
                    images: $mediaCount[MediaType::Image->value] ?? 0,
                    files: $mediaCount[MediaType::PDF->value] ?? 0,
                    pages: $articlesCount[ArticleType::Page->value] ?? 0,
                    blogPosts: $articlesCount[ArticleType::Blog->value] ?? 0,
                    users: $userCount,
                    albums: $albumCount,
                    visitorsToday: count($visitorsToday),
                    pageViewsToday: $reportPageViewsToday->total,
                ),
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
            template: 'core/backoffice/user-list',
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
            template: 'core/backoffice/user-edit',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalNew') . ' ' .
                    $localisationUtil->getValue('globalUser'),
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
            template: 'core/backoffice/user-edit',
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
        $articles = $this->articleService->filterArticleBy(
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
            template: 'core/backoffice/article-list',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminArticles'),
                articles: $articles,
                pagination: $pagination,
            ),
        );
    }

    /**
     * Endpoint: /backoffice/albums
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getAlbumsPage(Request $request): Response
    {
        $statusIdParam = $request->getGetParam('status');
        $albumStatus = $statusIdParam && AlbumStatus::tryFrom($statusIdParam)
            ? AlbumStatus::from($statusIdParam)
            : null;

        $languageIsoCodeParam = $request->getGetParam('lang');
        $albumLanguage = $languageIsoCodeParam && Language::tryFrom($languageIsoCodeParam)
            ? Language::from($languageIsoCodeParam)
            : null;

        $albums = $this->albumService->filterAlbumBy(
            languageIsoCodes: $albumLanguage ? [$albumLanguage->value] : [],
            statusIds: $albumStatus
                ? [$albumStatus->value]
                : [
                    AlbumStatus::Published->value,
                    AlbumStatus::Private->value,
                    AlbumStatus::Unlisted->value,
                    AlbumStatus::Draft->value,
                ],
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy(field: 'id', direction: QueryOrderDirection::DESC)],
            )
        );

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/album-list',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminAlbums'),
                albums: $albums,
            ),
        );
    }

    /**
     * Endpoint: /backoffice/albums/{albumId}
     * Method: GET
     *
     * @param int $albumId
     * @param Request $request
     * @return Response
     */
    protected function getViewAlbumPage(int $albumId, Request $request): Response
    {
        $album = $this->albumService->getAlbumForId(
            id: $albumId,
            includeSections: true,
            includeMedia: true,
        );

        if (!$album) {
            return Response::createNotFoundResponse($request);
        }

        return Response::createHtmlResponse(
            template: 'core/backoffice/album-view',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $album->titleHtml,
                album: $album,
            ),
        );
    }

    /**
     * Endpoint: /backoffice/albums/{albumId}/edit
     * Method: GET
     *
     * @param int $albumId
     * @param Request $request
     * @return Response
     */
    protected function getEditAlbumPage(int $albumId, Request $request): Response
    {
        $album = $this->albumService->getAlbumForId($albumId);
        if (!$album) {
            return Response::createNotFoundResponse($request);
        }

        return Response::createHtmlResponse(
            template: 'core/backoffice/album-edit',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $album->titleHtml,
                album: $album,
            ),
        );
    }

    /**
     * Endpoint: /backoffice/albums/new
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getNewAlbumPage(Request $request): Response
    {
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/album-edit',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminAlbums'),
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
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/article-edit',
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
            template: 'core/backoffice/article-edit',
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
     * Endpoint: /backoffice/articles/{articleId}/preview
     * Method: GET
     *
     * @param int $articleId
     * @param Request $request
     * @return Response
     */
    protected function getArticlePreviewPage(int $articleId, Request $request): Response
    {
        $article = $this->articleService->getArticleForId($articleId);
        if (!$article) {
            return Response::createNotFoundResponse($request);
        }

        $siteImage = $article->mainImageId
            ? $this->mediaService->getMediaForId($article->mainImageId)
            : null;

        return Response::createHtmlResponse(
            template: 'app/frontend/public/article-view',
            responseData: new HtmlResponseData(
                request: $request,
                pageTitle: $article->title,
                pageDescription: $article->getContentExcerpt(),
                siteImageUrl: $siteImage?->getPathWithNameLarge(),
                article: $article,
                postBottomContent: $this->articleService->getPageContent(
                    type: PageContentType::BlogBottom,
                    language: $request->siteLanguage,
                ),
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
                orderBy: [new QueryOrderBy('id', QueryOrderDirection::DESC)],
                pagination: new Response\Pagination(itemsPerPage: 100),
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
            typeIds: [
                MediaType::PDF->value,
                MediaType::CSV->value,
                MediaType::Unknown->value,
            ],
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
     * @param string|null $url
     * @param string|null $device
     * @param string|null $browser
     * @param string|null $countryIsoCode
     * @param string|null $languageIsoCode
     * @param int|null $itemsCount
     * @param Request $request
     * @return Response
     */
    protected function getAnalyticsPage(
        ?string $period,
        ?string $date,
        ?int $eventTypeId,
        ?string $url,
        ?string $device,
        ?string $browser,
        ?string $countryIsoCode,
        ?string $languageIsoCode,
        ?int $itemsCount,
        Request $request,
    ): Response {
        $period = $period && Period::tryFrom($period) ? Period::from($period) : Period::Day;
        if (!$date || !DateUtil::isValidDateISO8601($date . 'T00:00:00Z')) {
            $now = new DateTimeImmutable();
            $date = $now->format('Y-m-d');
        }

        $from = Period::getFrom($period, $date);
        $to = Period::getTo($period, $from);
        $limit = $itemsCount ?: 50;

        $eventType = $eventTypeId && EventType::tryFrom($eventTypeId)
            ? EventType::from($eventTypeId)
            : null;

        $reportPageViews = $this->analyticsService->countPageViews(
            from: $from,
            to: $to,
            period: $period,
            eventType: $eventType,
            url: $url,
            device: $device,
            browser: $browser,
            countryIsoCode: $countryIsoCode,
            languageIsoCode: $languageIsoCode,
        );

        $reportVisitors = $this->analyticsService->countPageViews(
            from: $from,
            to: $to,
            period: $period,
            eventType: $eventType,
            url: $url,
            device: $device,
            browser: $browser,
            countryIsoCode: $countryIsoCode,
            languageIsoCode: $languageIsoCode,
            columnName: CountDbColumn::Visitor,
        );

        $visitorsTotal = $this->analyticsService->countTop(
            columnName: CountDbColumn::Visitor,
            from: $from,
            to: $to,
            eventType: $eventType,
            url: $url,
            device: $device,
            browser: $browser,
            countryIsoCode: $countryIsoCode,
            languageIsoCode: $languageIsoCode,
            limit: 1000000,
        );

        $pages = $this->analyticsService->countTop(
            columnName: CountDbColumn::Page,
            from: $from,
            to: $to,
            eventType: $eventType,
            url: $url,
            device: $device,
            browser: $browser,
            countryIsoCode: $countryIsoCode,
            languageIsoCode: $languageIsoCode,
            limit: $limit,
        );

        $countries = $this->analyticsService->countTop(
            columnName: CountDbColumn::Country,
            from: $from,
            to: $to,
            eventType: $eventType,
            url: $url,
            device: $device,
            browser: $browser,
            countryIsoCode: $countryIsoCode,
            languageIsoCode: $languageIsoCode,
            limit: $limit,
        );

        $sources = $this->analyticsService->countTop(
            columnName: CountDbColumn::Referrer,
            from: $from,
            to: $to,
            eventType: $eventType,
            url: $url,
            device: $device,
            browser: $browser,
            countryIsoCode: $countryIsoCode,
            languageIsoCode: $languageIsoCode,
            limit: $limit,
        );

        $devices = $this->analyticsService->countTop(
            columnName: CountDbColumn::Device,
            from: $from,
            to: $to,
            eventType: $eventType,
            url: $url,
            device: $device,
            browser: $browser,
            countryIsoCode: $countryIsoCode,
            languageIsoCode: $languageIsoCode,
            limit: $limit,
        );

        $browsers = $this->analyticsService->countTop(
            columnName: CountDbColumn::Browser,
            from: $from,
            to: $to,
            eventType: $eventType,
            url: $url,
            device: $device,
            browser: $browser,
            countryIsoCode: $countryIsoCode,
            languageIsoCode: $languageIsoCode,
            limit: $limit,
        );

        $languages = $this->analyticsService->countTop(
            columnName: CountDbColumn::Language,
            from: $from,
            to: $to,
            eventType: $eventType,
            url: $url,
            device: $device,
            browser: $browser,
            countryIsoCode: $countryIsoCode,
            languageIsoCode: $languageIsoCode,
            limit: $limit,
        );

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/analytics',
            responseData: new HtmlResponseDataAnalytics(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminAnalytics'),
                reportPageViews: $reportPageViews,
                reportVisitors: $reportVisitors,
                visitors: $visitorsTotal,
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
            template: 'core/backoffice/page-content-list',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('pageContentEditTitle'),
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

    /**
     * Endpoint: /backoffice/emails
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getEmailsAdminPage(Request $request): Response
    {
        $limit = $request->getGetParam('limit') ?? 50;

        $emails = $this->mailerService->filterMailerItemBy(
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy('id', QueryOrderDirection::DESC)],
                pagination: new Response\Pagination(itemsPerPage: $limit),
            ),
        );

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/email-list',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminEmails'),
                emails: $emails,
            ),
        );
    }
}
