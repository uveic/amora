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
        $session = $request->getSession();
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
        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());

        return Response::createBackofficeHtmlResponse(
            'dashboard',
            new HtmlResponseData(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminDashboard'),
            )
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
        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());
        $users = $this->userService->filterUsersBy();
        return Response::createBackofficeHtmlResponse(
            'users',
            new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminUsers'),
                usersList: $users
            )
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
        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());

        return Response::createBackofficeHtmlResponse(
            'users-edit',
            new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalNew') . ' ' .
                    $localisationUtil->getValue('globalUser')
            )
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
        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());
        $user = $this->userService->getUserForId($userId, true);
        if (empty($user)) {
            return Response::createFrontendPublicHtmlResponse(
                'shared/404',
                new HtmlResponseDataAuthorised($request)
            );
        }

        return Response::createBackofficeHtmlResponse(
            'users-edit',
            new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalEdit') . ' ' .
                    $localisationUtil->getValue('globalUser'),
                usersList: [$user]
            )
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
        $articles = $this->articleService->filterArticlesBy(
            typeIds: [ArticleType::PAGE],
            includeTags: true,
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy('updated_at', 'DESC')],
                limit: 100
            )
        );
        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());

        return Response::createBackofficeHtmlResponse(
            'articles',
            new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminArticles'),
                articles: $articles
            )
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

        if ($articleTypeIdGetParam === ArticleType::HOMEPAGE) {
            $articles = $this->articleService->getArticlesForTypeIds([$articleTypeIdGetParam]);
            if ($articles) {
                return Response::createRedirectResponse(
                    UrlBuilderUtil::getBackofficeArticleUrl(
                        languageIsoCode: $request->getSiteLanguage(),
                        articleId: $articles[0]->getId()
                    )
                );
            }
        }

        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());
        return Response::createBackofficeHtmlResponse(
            'articles-edit',
            new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalNew') . ' ' .
                    $localisationUtil->getValue('globalArticle')
            )
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
                'shared/404',
                new HtmlResponseDataAuthorised($request)
            );
        }

        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());
        $articleSections = $this->articleService->getSectionsForArticleId($articleId);
        return Response::createBackofficeHtmlResponse(
            'articles-edit',
            new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalEdit') . ' ' .
                    $localisationUtil->getValue('globalArticle'),
                articles: [$article],
                articleSections: $articleSections
            )
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
        $images = $this->imageService->getAllImages();
        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());

        return Response::createBackofficeHtmlResponse(
            'images',
            new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminImages'),
                images: $images
            )
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
        $articles = $this->articleService->filterArticlesBy(
            typeIds: [ArticleType::BLOG],
            includeTags: true,
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy('updated_at', 'DESC')],
                limit: 100
            )
        );
        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());

        return Response::createBackofficeHtmlResponse(
            'blog-posts',
            new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('navAdminBlogPosts'),
                articles: $articles
            )
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
        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());
        return Response::createBackofficeHtmlResponse(
            'blog-posts-edit',
            new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalNew') . ' ' .
                    $localisationUtil->getValue('globalBlogPost'),
            )
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
                'shared/404',
                new HtmlResponseDataAuthorised($request)
            );
        }

        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());
        $articleSections = $this->articleService->getSectionsForArticleId($articleId);
        return Response::createBackofficeHtmlResponse(
            'blog-posts-edit',
            new HtmlResponseDataAuthorised(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalEdit') . ' ' .
                    $localisationUtil->getValue('globalBlogPost'),
                articles: [$article],
                articleSections: $articleSections,
            )
        );
    }
}
