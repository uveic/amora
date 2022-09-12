<?php

namespace Amora\Core\Router;

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Entity\Response\HtmlResponseDataAuthorised;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Service\MediaService;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\QueryOrderDirection;

final class BackofficeHtmlController extends BackofficeHtmlControllerAbstract
{
    public function __construct(
        private UserService $userService,
        private ArticleService $articleService,
        private MediaService $mediaService,
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
            responseData: new HtmlResponseData(
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
            responseData: new HtmlResponseDataAuthorised(
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
            responseData: new HtmlResponseDataAuthorised(
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
            return Response::createHtmlResponse(
                template: 'app/frontend/public/404',
                responseData: new HtmlResponseDataAuthorised($request),
            );
        }

        return Response::createHtmlResponse(
            template: 'core/backoffice/users-edit',
            responseData: new HtmlResponseDataAuthorised(
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
            responseData: new HtmlResponseDataAuthorised(
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

        if (ArticleType::isPartialContent($articleType)) {
            $articles = $this->articleService->filterArticlesBy(
                languageIsoCodes: [$request->siteLanguage->value],
                typeIds: [$articleType->value],
            );

            if ($articles) {
                return Response::createRedirectResponse(
                    UrlBuilderUtil::buildBackofficeArticleUrl(
                        language: $request->siteLanguage,
                        articleId: $articles[0]->id,
                    )
                );
            }
        }

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createHtmlResponse(
            template: 'core/backoffice/articles-edit',
            responseData: new HtmlResponseDataAuthorised(
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
            return Response::createHtmlResponse(
                template: 'app/frontend/public/404',
                responseData: new HtmlResponseDataAuthorised($request),
            );
        }

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        $articleSections = $this->articleService->getSectionsForArticleId($articleId);
        return Response::createHtmlResponse(
            template: 'core/backoffice/articles-edit',
            responseData: new HtmlResponseDataAuthorised(
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
            responseData: new HtmlResponseDataAuthorised(
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
            responseData: new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminMedia'),
                files: $files
            ),
        );
    }
}
