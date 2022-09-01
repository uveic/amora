<?php

namespace Amora\Core\Router;

use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlHomepageResponseData;
use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Service\XmlService;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\QueryOrderDirection;
use DateTimeImmutable;

final class PublicHtmlController extends PublicHtmlControllerAbstract
{
    public function __construct(
        private UserService $userService,
        private ArticleService $articleService,
        private XmlService $xmlService,
    ) {
        parent::__construct();
    }

    protected function authenticate(Request $request): bool
    {
        return true;
    }

    /**
     * Endpoint: /home
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getHomePage(Request $request): Response
    {
        return $this->buildHomepageResponse($request);
    }

    /**
     * Endpoint: /login
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getLoginPage(Request $request): Response
    {
        $session = $request->session;
        if ($session && $session->isAdmin()) {
            return Response::createRedirectResponse(
                url: UrlBuilderUtil::buildBackofficeDashboardUrl($request->siteLanguage),
            );
        }

        if ($session && $session->isAuthenticated()) {
            return Response::createRedirectResponse(
                url: UrlBuilderUtil::buildAppDashboardUrl($request->siteLanguage),
            );
        }

        return Response::createHtmlResponse(
            template: 'core/frontend/public/login',
            responseData: new HtmlResponseData(
                request: $request,
                pageTitle: Core::getLocalisationUtil($request->siteLanguage)
                    ->getValue('formLoginAction'),
            ),
        );
    }

    /**
     * Endpoint: /login/forgot
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getForgotPasswordPage(Request $request): Response
    {
        $session = $request->session;
        $isAuthenticated = $session && $session->isAuthenticated();
        if ($isAuthenticated) {
            return Response::createRedirectResponse(
                url: UrlBuilderUtil::buildBackofficeDashboardUrl($request->siteLanguage),
            );
        }

        return Response::createHtmlResponse(
            template: 'core/frontend/public/login-forgot',
            responseData: new HtmlResponseData(
                request: $request,
                pageTitle: Core::getLocalisationUtil($request->siteLanguage)
                    ->getValue('authenticationForgotPassword'),
            ),
        );
    }

    /**
     * Endpoint: /register
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getRegistrationPage(Request $request): Response
    {
        $session = $request->session;
        $isAuthenticated = $session && $session->isAuthenticated();
        if ($isAuthenticated) {
            return Response::createRedirectResponse(
                url: $session->isAdmin()
                    ? UrlBuilderUtil::buildBackofficeDashboardUrl($request->siteLanguage)
                    : UrlBuilderUtil::buildAppDashboardUrl($request->siteLanguage)
            );
        }

        $isRegistrationEnabled = Core::getConfig()->isRegistrationEnabled;
        if (!$isRegistrationEnabled) {
            $isInvitationEnabled = Core::getConfig()->isInvitationEnabled;
            if ($isInvitationEnabled) {
                return Response::createRedirectResponse(
                    url: UrlBuilderUtil::buildPublicInviteRequestUrl($request->siteLanguage),
                );
            }

            return Response::createRedirectResponse(
                url: UrlBuilderUtil::buildPublicHomepageUrl($request->siteLanguage),
            );
        }

        return Response::createHtmlResponse(
            template: 'core/frontend/public/register',
            responseData: new HtmlResponseData(
                request: $request,
                pageTitle: Core::getLocalisationUtil($request->siteLanguage)->getValue('navSignUp'),
            ),
        );
    }

    /**
     * Endpoint: /user/verify/{verificationIdentifier}
     * Method: POST
     *
     * @param string $verificationIdentifier
     * @param Request $request
     * @return Response
     */
    protected function getUserVerifiedHtml(
        string $verificationIdentifier,
        Request $request
    ): Response {
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        $feedback = $this->userService->verifyEmailAddress(
            $verificationIdentifier,
            $localisationUtil
        );

        return $this->buildHomepageResponse(
            request: $request,
            feedback: $feedback,
        );
    }

    /**
     * Endpoint: /user/reset/{verificationIdentifier}
     * Method: GET
     *
     * @param string $verificationIdentifier
     * @param Request $request
     * @return Response
     */
    protected function getPasswordChangeHtml(
        string $verificationIdentifier,
        Request $request
    ): Response {
        $res = $this->userService->validatePasswordResetVerificationPage($verificationIdentifier);
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);

        if (empty($res)) {
            return $this->buildHomepageResponse(
                request: $request,
                feedback: new Feedback(
                    false,
                    $localisationUtil->getValue('authenticationPasswordResetLinkError')
                )
            );
        }

        $user = $this->userService->getUserForId($res->userId);
        return Response::createHtmlResponse(
            template: 'core/frontend/public/password-reset',
            responseData: new HtmlResponseData(
                request: $request,
                pageTitle: $localisationUtil->getValue('navChangePassword'),
                verificationHash: $user->getValidationHash(),
                passwordUserId: $user->id,
            ),
        );
    }

    /**
     * Endpoint: /user/create/{verificationIdentifier}
     * Method: GET
     *
     * @param string $verificationIdentifier
     * @param Request $request
     * @return Response
     */
    protected function getCreateUserPasswordHtml(
        string $verificationIdentifier,
        Request $request
    ): Response {
        $res = $this->userService->validateCreateUserPasswordPage($verificationIdentifier);
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);

        if (empty($res)) {
            return $this->buildHomepageResponse(
                request: $request,
                feedback: new Feedback(
                    isSuccess: false,
                    message: $localisationUtil->getValue('authenticationPasswordCreationLinkError'),
                ),
            );
        }

        $user = $this->userService->getUserForId($res->userId);
        return Response::createHtmlResponse(
            template: 'core/frontend/public/password-creation',
            responseData: new HtmlResponseData(
                request: $request,
                pageTitle: $localisationUtil->getValue('navCreatePassword'),
                verificationHash: $user->getValidationHash(),
                passwordUserId: $user->id,
            ),
        );
    }

    /**
     * Endpoint: /invite-request
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getInviteRequestPage(Request $request): Response
    {
        $session = $request->session;
        $isAuthenticated = $session && $session->isAuthenticated();
        if ($isAuthenticated) {
            return Response::createRedirectResponse(
                url: UrlBuilderUtil::buildBackofficeDashboardUrl($request->siteLanguage),
            );
        }

        if (!Core::getConfig()->isInvitationEnabled) {
            return Response::createRedirectResponse(
                url: UrlBuilderUtil::buildPublicHomepageUrl($request->siteLanguage),
            );
        }

        return Response::createHtmlResponse(
            template: 'core/frontend/public/invite-request',
            responseData: new HtmlResponseData(
                request: $request,
                pageTitle: Core::getLocalisationUtil($request->siteLanguage)->getValue('navSignUp'),
            ),
        );
    }

    /**
     * Endpoint: /rss
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getRss(Request $request): Response
    {
        $articles = $this->articleService->filterArticlesBy(
            statusIds: [ArticleStatus::Published->value],
            typeIds: [ArticleType::Blog->value],
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy(field: 'published_at', direction: QueryOrderDirection::DESC)],
                pagination: new Response\Pagination(itemsPerPage: 10),
            ),
        );

        $xml = $this->xmlService->buildRss(
            localisationUtil: Core::getLocalisationUtil(Core::getDefaultLanguage()),
            articles: $articles,
        );

        return Response::createSuccessXmlResponse($xml);
    }

    /**
     * Endpoint: /sitemap
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getSitemap(Request $request): Response
    {
        $articles = $this->articleService->filterArticlesBy(
            languageIsoCodes: [Core::getDefaultLanguage()->value],
            statusIds: [ArticleStatus::Published->value],
            typeIds: [ArticleType::Page->value],
            publishedBefore: new DateTimeImmutable(),
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy(field: 'published_at', direction: QueryOrderDirection::DESC)],
            ),
        );

        $xml = $this->xmlService->buildSitemap(
            articles: $articles,
        );

        return Response::createSuccessXmlResponse($xml);
    }

    private function buildHomepageResponse(
        Request $request,
        ?Feedback $feedback = null,
    ): Response {
        $isAdmin = $request->session && $request->session->isAdmin();
        $statusIds = $isAdmin
            ? [ArticleStatus::Published->value, ArticleStatus::Private->value]
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

        $homepageArticle = $this->articleService->getArticlePartialContent(
            articleType: ArticleType::PartialContentHomepage,
            language: $request->siteLanguage,
        );

        return Response::createHtmlResponse(
            template: 'app/frontend/public/home',
            responseData: new HtmlHomepageResponseData(
                request: $request,
                pagination: $pagination,
                homepageContent: $homepageArticle,
                homeArticles: [],
                blogArticles: $blogArticles,
                feedback: $feedback,
            ),
        );
    }
}
