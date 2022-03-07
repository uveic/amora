<?php

namespace Amora\Core\Router;

use Amora\Core\Core;
use Amora\Core\Model\Response\HtmlHomepageResponseData;
use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Model\Response\UserFeedback;
use Amora\Core\Model\Util\QueryOptions;
use Amora\Core\Model\Util\QueryOrderBy;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Service\RssService;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Model\Request;
use Amora\Core\Model\Response;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\QueryOrderDirection;

final class PublicHtmlController extends PublicHtmlControllerAbstract
{
    public function __construct(
        private UserService $userService,
        private ArticleService $articleService,
        private RssService $rssService,
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

        return Response::createFrontendPublicHtmlResponse(
            template: 'shared/login',
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

        return Response::createFrontendPublicHtmlResponse(
            template: 'shared/login-forgot',
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

        return Response::createFrontendPublicHtmlResponse(
            template: 'shared/register',
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
        $userFeedback = $this->userService->verifyEmailAddress(
            $verificationIdentifier,
            $localisationUtil
        );

        return $this->buildHomepageResponse(
            request: $request,
            userFeedback: $userFeedback,
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
                userFeedback: new UserFeedback(
                    false,
                    $localisationUtil->getValue('authenticationPasswordResetLinkError')
                )
            );
        }

        $user = $this->userService->getUserForId($res->userId);
        return Response::createFrontendPublicHtmlResponse(
            template: 'shared/password-reset',
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
            return Response::createFrontendPublicHtmlResponse(
                template: 'shared/home',
                responseData: new HtmlHomepageResponseData(
                    request: $request,
                    userFeedback: new UserFeedback(
                        isSuccess: false,
                        message: $localisationUtil->getValue('authenticationPasswordCreationLinkError'),
                    ),
                )
            );
        }

        $user = $this->userService->getUserForId($res->userId);
        return Response::createFrontendPublicHtmlResponse(
            template: 'shared/password-creation',
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

        return Response::createFrontendPublicHtmlResponse(
            template: 'shared/invite-request',
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

        $xml = $this->rssService->buildRss(
            siteLanguage: $request->siteLanguage,
            articles: $articles,
        );

        return Response::createSuccessXmlResponse($xml);
    }

    private function buildHomepageResponse(
        Request $request,
        ?UserFeedback $userFeedback = null,
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

        return Response::createFrontendPublicHtmlResponse(
            template: 'shared/home',
            responseData: new HtmlHomepageResponseData(
                request: $request,
                pagination: $pagination,
                homepageContent: $homepageArticle,
                homeArticles: [],
                blogArticles: $blogArticles,
                userFeedback: $userFeedback,
            ),
        );
    }
}
