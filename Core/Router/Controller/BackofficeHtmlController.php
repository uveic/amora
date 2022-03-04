<?php

namespace Amora\Core\Router;

use Amora\Core\Core;
use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Model\Request;
use Amora\Core\Model\Response;
use Amora\Core\Model\Util\QueryOptions;
use Amora\Core\Model\Util\QueryOrderBy;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Service\ImageService;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\QueryOrderDirection;

final class BackofficeHtmlController extends BackofficeHtmlControllerAbstract
{
    public function __construct(
        private UserService $userService,
        private ArticleService $articleService,
        private ImageService $imageService,
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

        return Response::createBackofficeHtmlResponse(
            template: 'dashboard',
            responseData: new HtmlResponseData(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminDashboard'),
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
        return Response::createBackofficeHtmlResponse(
            template: 'users',
            responseData: new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminUsers'),
                listOfUsers: $users,
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

        return Response::createBackofficeHtmlResponse(
            template: 'users-edit',
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
            return Response::createFrontendPublicHtmlResponse(
                template: 'shared/404',
                responseData: new HtmlResponseDataAuthorised($request),
            );
        }

        return Response::createBackofficeHtmlResponse(
            template: 'users-edit',
            responseData: new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalEdit') . ' ' .
                    $localisationUtil->getValue('globalUser'),
                listOfUsers: [$user],
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
        $pagination = new Response\Pagination(itemsPerPage: 25);
        $articles = $this->articleService->filterArticlesBy(
            typeIds: [ArticleType::Page->value],
            includeTags: true,
            includePublishedAtInTheFuture: true,
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy(field: 'updated_at', direction: QueryOrderDirection::DESC)],
                pagination: $pagination,
            )
        );
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);

        return Response::createBackofficeHtmlResponse(
            template: 'articles',
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
        $articleTypeIdGetParam = $request->getGetParam('articleType')
            ? (int)$request->getGetParam('articleType')
            : null;

        if ($articleTypeIdGetParam === ArticleType::Homepage->value) {
            $articles = $this->articleService->filterArticlesBy(typeIds: [$articleTypeIdGetParam]);
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
        return Response::createBackofficeHtmlResponse(
            template: 'articles-edit',
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
            return Response::createFrontendPublicHtmlResponse(
                template: 'shared/404',
                responseData: new HtmlResponseDataAuthorised($request),
            );
        }

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        $articleSections = $this->articleService->getSectionsForArticleId($articleId);
        return Response::createBackofficeHtmlResponse(
            template: 'articles-edit',
            responseData: new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalEdit') . ' ' .
                    $localisationUtil->getValue('globalArticle'),
                articles: [$article],
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
        $images = $this->imageService->filterImagesBy(
            queryOptions: new QueryOptions(
                pagination: new Response\Pagination(itemsPerPage: 50),
            ),
        );
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);

        return Response::createBackofficeHtmlResponse(
            template: 'images',
            responseData: new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminImages'),
                images: $images
            ),
        );
    }

    /**
     * Endpoint: /backoffice/blog-posts
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getBlogPostsPage(Request $request): Response
    {
        $pagination = new Response\Pagination(itemsPerPage: 25);
        $articles = $this->articleService->filterArticlesBy(
            typeIds: [ArticleType::Blog->value],
            includeTags: true,
            includePublishedAtInTheFuture: true,
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy(field: 'updated_at', direction: QueryOrderDirection::DESC)],
                pagination: $pagination,
            )
        );
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);

        return Response::createBackofficeHtmlResponse(
            template: 'blog-posts',
            responseData: new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminBlogPosts'),
                articles: $articles,
                pagination: $pagination,
            ),
        );
    }

    /**
     * Endpoint: /backoffice/blog-posts/new
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getNewBlogPostPage(Request $request): Response
    {
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createBackofficeHtmlResponse(
            template: 'blog-posts-edit',
            responseData: new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalNew') . ' ' .
                    $localisationUtil->getValue('globalBlogPost'),
            ),
        );
    }

    /**
     * Endpoint: /backoffice/blog-posts/{articleId}
     * Method: GET
     *
     * @param int $articleId
     * @param Request $request
     * @return Response
     */
    protected function getEditBlogPostPage(int $articleId, Request $request): Response
    {
        $article = $this->articleService->getArticleForId($articleId, true);
        if (empty($article)) {
            return Response::createFrontendPublicHtmlResponse(
                template: 'shared/404',
                responseData: new HtmlResponseDataAuthorised($request),
            );
        }

        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        $articleSections = $this->articleService->getSectionsForArticleId($articleId);
        return Response::createBackofficeHtmlResponse(
            template: 'blog-posts-edit',
            responseData: new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalEdit') . ' ' .
                    $localisationUtil->getValue('globalBlogPost'),
                articles: [$article],
                articleSections: $articleSections,
            ),
        );
    }
}
