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
                UrlBuilderUtil::buildBackofficeDashboardUrl($request->siteLanguageIsoCode)
            );
        }

        if ($session && $session->isAuthenticated()) {
            return Response::createRedirectResponse(
                UrlBuilderUtil::buildAppDashboardUrl($request->siteLanguageIsoCode)
            );
        }

        return Response::createFrontendPublicHtmlResponse(
            'shared/login',
            new HtmlResponseData(
                request: $request,
                pageTitle: Core::getLocalisationUtil($request->siteLanguageIsoCode)
                    ->getValue('formLoginAction'),
            )
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
                UrlBuilderUtil::buildBackofficeDashboardUrl($request->siteLanguageIsoCode)
            );
        }

        return Response::createFrontendPublicHtmlResponse(
            'shared/login-forgot',
            new HtmlResponseData(
                request: $request,
                pageTitle: Core::getLocalisationUtil($request->siteLanguageIsoCode)
                    ->getValue('authenticationForgotPassword'),
            )
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
                $session->isAdmin()
                    ? UrlBuilderUtil::buildBackofficeDashboardUrl($request->siteLanguageIsoCode)
                    : UrlBuilderUtil::buildAppDashboardUrl($request->siteLanguageIsoCode)
            );
        }

        $isRegistrationEnabled = Core::getConfigValue('registrationEnabled');
        if (!$isRegistrationEnabled) {
            $isInvitationEnabled = Core::getConfigValue('invitationEnabled');
            if ($isInvitationEnabled) {
                return Response::createRedirectResponse(
                    UrlBuilderUtil::buildPublicInviteRequestUrl($request->siteLanguageIsoCode)
                );
            }

            return Response::createRedirectResponse(
                UrlBuilderUtil::buildPublicHomepageUrl($request->siteLanguageIsoCode)
            );
        }

        return Response::createFrontendPublicHtmlResponse(
            'shared/register',
            new HtmlResponseData(
                request: $request,
                pageTitle: Core::getLocalisationUtil($request->siteLanguageIsoCode)->getValue('navSignUp'),
            )
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
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguageIsoCode);
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
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguageIsoCode);

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
                passwordUserId: $user->getId()
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
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguageIsoCode);

        if (empty($res)) {
            return Response::createFrontendPublicHtmlResponse(
                'shared/home',
                new HtmlHomepageResponseData(
                    request: $request,
                    userFeedback: new UserFeedback(
                        false,
                        $localisationUtil->getValue('authenticationPasswordCreationLinkError')
                    )
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
                passwordUserId: $user->getId()
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
                UrlBuilderUtil::buildBackofficeDashboardUrl($request->siteLanguageIsoCode)
            );
        }

        $isInvitationEnabled = Core::getConfigValue('invitationEnabled');
        if (!$isInvitationEnabled) {
            return Response::createRedirectResponse(
                UrlBuilderUtil::buildPublicHomepageUrl($request->siteLanguageIsoCode)
            );
        }

        return Response::createFrontendPublicHtmlResponse(
            'shared/invite-request',
            new HtmlResponseData(
                request: $request,
                pageTitle: Core::getLocalisationUtil($request->siteLanguageIsoCode)->getValue('navSignUp'),
            )
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
                orderBy: [new QueryOrderBy('published_at', 'DESC')],
                pagination: new Response\Pagination(itemsPerPage: 10),
            ),
        );

        $xml = $this->rssService->buildRss(
            siteLanguage: $request->siteLanguageIsoCode,
            articles: $articles,
        );

        return Response::createSuccessXmlResponse($xml);
    }

    private function buildHomepageResponse(
        Request $request,
        ?UserFeedback $userFeedback = null,
    ): Response {
        // ToDo: Move tagIdsForHomepage to some kind of settings
        $tagIds = Core::getConfigValue('tagIdsForHomepage') ?? [];
        $homeArticles = $this->articleService->filterArticlesBy(
            statusIds: [ArticleStatus::Published->value],
            typeIds: [ArticleType::Page->value],
            tagIds: $tagIds,
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy('published_at', 'DESC')],
                pagination: new Response\Pagination(itemsPerPage: 10),
            ),
        );

        $isAdmin = $request->session && $request->session->isAdmin();
        $statusIds = $isAdmin
            ? [ArticleStatus::Published->value, ArticleStatus::Private->value]
            : [ArticleStatus::Published->value];
        $pagination = new Response\Pagination(itemsPerPage: 15);
        $blogArticles = $this->articleService->filterArticlesBy(
            statusIds: $statusIds,
            typeIds: [ArticleType::Blog->value],
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy('published_at', 'DESC')],
                pagination: $pagination,
            ),
        );

        $homepageArticle = $this->articleService->getHomepageArticle();

        return Response::createFrontendPublicHtmlResponse(
            'shared/home',
            new HtmlHomepageResponseData(
                request: $request,
                homepageContent: $homepageArticle,
                homeArticles: $homeArticles,
                blogArticles: $blogArticles,
                userFeedback: $userFeedback,
                pagination: $pagination,
            )
        );
    }
}
