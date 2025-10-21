<?php

namespace Amora\Core\Router;

use Amora\App\Module\Form\Entity\PageContent;
use Amora\App\Value\AppPageContentType;
use Amora\App\Value\AppUserRole;
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
use Amora\Core\Module\Album\Model\Collection;
use Amora\Core\Module\Album\Service\AlbumService;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Module\Analytics\Service\AnalyticsService;
use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Module\Analytics\Value\Parameter;
use Amora\Core\Module\Analytics\Value\Period;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Service\MediaService;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\Article\Value\PageContentSection;
use Amora\Core\Module\Article\Value\PageContentType;
use Amora\Core\Module\Mailer\Service\MailerService;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Service\SessionService;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Module\User\Value\UserStatus;
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
        if (!$request->session || !$request->session->isAdmin()) {
            return false;
        }

        return $this->sessionService->updateSessionExpiryDateAndValidUntil(
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
        $mediaCountByTypeId = $this->mediaService->getMediaCountByTypeId();
        $userCount = $this->userService->getTotalUsers();
        $albumCount = $this->albumService->getTotalAlbums();

        $now = new DateTimeImmutable();
        $fromToday = DateUtil::convertStringToDateTimeImmutable($now->format('Y-m-d 00:00:00'));
        $toToday = DateUtil::convertStringToDateTimeImmutable($now->format('Y-m-d 23:59:59'));

        $visitorsToday = $this->analyticsService->calculateTotalAggregatedBy(
            parameter: Parameter::VisitorHash,
            from: $fromToday,
            to: $toToday,
            eventType: EventType::Visitor,
            limit: 1000000,
        );

        $reportPageViewsToday = $this->analyticsService->getReportViewCount(
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
                    images: $mediaCountByTypeId[MediaType::Image->value] ?? 0,
                    files: ($mediaCountByTypeId[MediaType::PDF->value] ?? 0) +
                        ($mediaCountByTypeId[MediaType::CSV->value] ?? 0) +
                        ($mediaCountByTypeId[MediaType::TXT->value] ?? 0) +
                        ($mediaCountByTypeId[MediaType::SVG->value] ?? 0) +
                        ($mediaCountByTypeId[MediaType::Unknown->value] ?? 0),
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

        $statusId = $request->getGetParam('sId');
        $userStatusIds = $statusId && UserStatus::tryFrom($statusId)
            ? [UserStatus::from($statusId)->value]
            : [
                UserStatus::Enabled->value,
                UserStatus::Disabled->value,
            ];

        $roleId = $request->getGetParam('rId');
        $userRoleIds = $roleId && (AppUserRole::tryFrom($roleId) || UserRole::tryFrom($roleId))
            ? (UserRole::tryFrom($roleId) ? [UserRole::from($roleId)->value] : [AppUserRole::from($roleId)->value])
            : [];

        $users = $this->userService->filterUserBy(
            statusIds: $userStatusIds,
            roleIds: $userRoleIds,
        );

        $userIds = [];
        /** @var User $user */
        foreach ($users as $user) {
            $userIds[] = $user->id;
        }

        $sessions = $userIds ? $this->sessionService->filterSessionBy(
            userIds: $userIds,
            queryOptions: new QueryOptions(
                orderBy: [
                    new QueryOrderBy('user_id', QueryOrderDirection::DESC),
                    new QueryOrderBy('last_visited_at', QueryOrderDirection::DESC),
                ],
            ),
        ) : [];

        return Response::createHtmlResponse(
            template: 'core/backoffice/user-list',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminUsers'),
                users: $users,
                sessions: $sessions,
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
                pageTitle: $localisationUtil->getValue('globalNew') . ' ' . $localisationUtil->getValue('globalUser'),
            ),
        );
    }

    /**
     * Endpoint: /backoffice/users/{userId}
     * Method: GET
     *
     * @param int $userId
     * @param Request $request
     * @return Response
     */
    protected function getUserViewPage(int $userId, Request $request): Response
    {
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        $user = $this->userService->getUserForId(userId: $userId, includeDisabled: true);
        if (empty($user)) {
            return Response::createNotFoundResponse($request);
        }

        $sessions = $this->sessionService->filterSessionBy(
            userIds: [$userId],
            queryOptions: new QueryOptions(
                orderBy: [
                    new QueryOrderBy('last_visited_at', QueryOrderDirection::DESC),
                ]
            ),
        );

        return Response::createHtmlResponse(
            template: 'core/backoffice/user-view',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalUser') . ': ' . $user->name,
                user: $user,
                sessions: $sessions,
            ),
        );
    }

    /**
     * Endpoint: /backoffice/users/{userId}/edit
     * Method: GET
     *
     * @param int $userId
     * @param Request $request
     * @return Response
     */
    protected function getUserEditPage(int $userId, Request $request): Response
    {
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        $user = $this->userService->getUserForId(userId: $userId, includeDisabled: true);
        if (empty($user)) {
            return Response::createNotFoundResponse($request);
        }

        return Response::createHtmlResponse(
            template: 'core/backoffice/user-edit',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalEdit') . ' ' . $localisationUtil->getValue('globalUser'),
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
            includeCollections: true,
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
                pageTitle: $localisationUtil->getValue('globalNew') . ' ' . $localisationUtil->getValue('globalArticle'),
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
                pageTitle: $localisationUtil->getValue('globalEdit') . ' ' . $localisationUtil->getValue('globalArticle'),
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
            template: 'app/public/article-view',
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
                pagination: new Response\Pagination(itemsPerPage: Core::SQL_QUERY_QTY),
            ),
        );

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/images',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminImages'),
                media: $images
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
            typeIds: MediaType::getAllNotImageIds(),
            statusIds: [MediaStatus::Active->value],
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy('id', QueryOrderDirection::DESC)],
                pagination: new Response\Pagination(itemsPerPage: Core::SQL_QUERY_QTY),
            ),
        );

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/media',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminMedia'),
                media: $files
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
     * @param int|null $paramId
     * @param int|null $eventId
     * @param int|null $itemsCount
     * @param Request $request
     * @return Response
     */
    protected function getAnalyticsPage(
        ?string $period,
        ?string $date,
        ?int $eventTypeId,
        ?int $paramId,
        ?int $eventId,
        ?int $itemsCount,
        Request $request,
    ): Response {
        $period = Period::getFromString($period);
        if (!$date || !DateUtil::isValidDateISO8601($date . 'T00:00:00Z')) {
            $now = new DateTimeImmutable();
            $date = $now->format('Y-m-d');
        }

        $from = $period->getFrom($date);
        $to = $period->getTo($from);
        $limit = $itemsCount ?: 50;

        $eventType = $eventTypeId && EventType::tryFrom($eventTypeId)
            ? EventType::from($eventTypeId)
            : null;

        $parameter = $paramId && Parameter::tryFrom($paramId)
            ? Parameter::from($paramId)
            : null;

        $reportPageViews = $this->analyticsService->getReportViewCount(
            from: $from,
            to: $to,
            period: $period,
            eventType: $eventType,
            parameter: $parameter,
            eventId: $eventId,
        );

        $reportVisitors = $this->analyticsService->getReportViewCount(
            from: $from,
            to: $to,
            period: $period,
            eventType: $eventType,
            parameter: $parameter,
            eventId: $eventId,
            includeVisitorHash: true,
        );

        $visitorsTotal = $this->analyticsService->calculateTotalAggregatedBy(
            parameter: Parameter::VisitorHash,
            from: $from,
            to: $to,
            eventType: $eventType,
            parameterQuery: $parameter,
            eventId: $eventId,
            limit: 1000000,
        );

        $pages = $this->analyticsService->calculateTotalAggregatedBy(
            parameter: Parameter::Url,
            from: $from,
            to: $to,
            eventType: $eventType,
            parameterQuery: $parameter,
            eventId: $eventId,
            limit: $limit,
        );

        $sources = $this->analyticsService->calculateTotalAggregatedBy(
            parameter: Parameter::Referrer,
            from: $from,
            to: $to,
            eventType: $eventType,
            parameterQuery: $parameter,
            eventId: $eventId,
            limit: $limit,
        );

        $devices = $this->analyticsService->calculateTotalAggregatedBy(
            parameter: Parameter::Platform,
            from: $from,
            to: $to,
            eventType: $eventType,
            parameterQuery: $parameter,
            eventId: $eventId,
            limit: $limit,
        );

        $browsers = $this->analyticsService->calculateTotalAggregatedBy(
            parameter: Parameter::Browser,
            from: $from,
            to: $to,
            eventType: $eventType,
            parameterQuery: $parameter,
            eventId: $eventId,
            limit: $limit,
        );

        $languages = $this->analyticsService->calculateTotalAggregatedBy(
            parameter: Parameter::Language,
            from: $from,
            to: $to,
            eventType: $eventType,
            parameterQuery: $parameter,
            eventId: $eventId,
            limit: $limit,
        );

        $eventValue = $parameter && $eventId
            ? $this->analyticsService->getEventValueForId(parameter: $parameter, id: $eventId)
            : null;

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/analytics',
            responseData: new HtmlResponseDataAnalytics(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminAnalytics'),
                reportPageViews: $reportPageViews,
                reportVisitors: $reportVisitors,
                parameterEventValue: $eventValue,
                visitors: $visitorsTotal,
                pages: $pages,
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
        $pageContentAll = $this->articleService->filterPageContentBy(
            typeIds: [$type->value],
        );

        $collectionId = null;
        $pageContentForLanguage = null;
        $hasCollection = AppPageContentType::displayContent($type, PageContentSection::Collection);

        /** @var PageContent $item */
        foreach ($pageContentAll as $item) {
            if (!$pageContentForLanguage && $item->language === $language) {
                $pageContentForLanguage = $item;
            }

            if (!$collectionId && $item->collection) {
                $collectionId = $item->collection->id;
            }

            if ($pageContentForLanguage && $collectionId) {
                break;
            }
        }

        if (!$pageContentForLanguage) {
            $collection = null;
            if ($hasCollection) {
                $collection = $collectionId
                    ? $this->albumService->getCollectionForId($collectionId)
                    : $this->albumService->storeCollection(Collection::getEmpty());
            }

            $pageContentForLanguage = $this->articleService->storePageContent(
                PageContent::getEmpty(
                    user: $request->session->user,
                    language: $language,
                    type: $type,
                    collection: $collection,
                ),
            );
        }

        $collectionMedia = $collectionId ? $this->albumService->filterCollectionMediaBy(collectionIds: [$collectionId]) : [];

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/page-content-edit',
            responseData: new HtmlResponseDataAdmin(
                request: $request,
                pageTitle: $localisationUtil->getValue('pageContentEditTitle' . $pageContentForLanguage->type->name),
                media: $collectionMedia,
                pageContentAll: $pageContentAll,
                pageContent: $pageContentForLanguage,
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
