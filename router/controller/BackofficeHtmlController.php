<?php

namespace uve\router;

use uve\core\Core;
use uve\core\Logger;
use uve\core\model\response\HtmlResponseDataAuthorised;
use uve\core\model\Request;
use uve\core\model\Response;
use uve\core\module\action\service\ActionService;
use uve\core\module\article\service\ArticleService;
use uve\core\module\article\service\ImageService;
use uve\core\module\user\service\SessionService;
use uve\core\module\user\service\UserService;

final class BackofficeHtmlController extends BackofficeHtmlControllerAbstract
{
    public function __construct(
        private Logger $logger,
        private ActionService $actionService,
        private SessionService $sessionService,
        private UserService $userService,
        private ArticleService $articleService,
        private ImageService $imageService,
    ) {
        parent::__construct();
    }

    protected function authenticate(Request $request): bool
    {
        $session = $request->getSession();
        $this->actionService->logAction($request, $session);
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
        return Response::createBackofficeHtmlResponse(
            'dashboard',
            new HtmlResponseDataAuthorised($request, 'Dashboard')
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
        $users = $this->userService->getListOfUsers();
        return Response::createBackofficeHtmlResponse(
            'users',
            new HtmlResponseDataAuthorised(
                $request,
                'Users',
                null,
                null,
                $users
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
        return Response::createBackofficeHtmlResponse(
            'users-edit',
            new HtmlResponseDataAuthorised($request, 'New User')
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
                $request,
                'Edit User',
                null,
                null,
                [$user]
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
        $articles = $this->articleService->getAllArticles();

        return Response::createBackofficeHtmlResponse(
            'articles',
            new HtmlResponseDataAuthorised(
                $request,
                'Articles',
                null,
                $articles
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
        return Response::createBackofficeHtmlResponse(
            'articles-edit',
            new HtmlResponseDataAuthorised($request, 'New Article')
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
        $article = $this->articleService->getArticleForId($articleId);
        if (empty($article)) {
            return Response::createFrontendPublicHtmlResponse(
                'shared/404',
                new HtmlResponseDataAuthorised($request)
            );
        }

        $articleSections = $this->articleService->getSectionsForArticleId($articleId);
        return Response::createBackofficeHtmlResponse(
            'articles-edit',
            new HtmlResponseDataAuthorised(
                $request,
                'Edit Article',
                null,
                [$article],
                [],
                [],
                $articleSections
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

        return Response::createBackofficeHtmlResponse(
            'images',
            new HtmlResponseDataAuthorised(
                $request,
                'Images',
                null,
                [],
                [],
                $images
            )
        );
    }
}
