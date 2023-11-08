<?php

namespace Amora\Core\Router;

use Amora\App\Router\AppPublicHtmlController;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Module\Album\Service\AlbumService;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Service\FeedService;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Util\UrlBuilderUtil;

final class PublicHtmlController extends PublicHtmlControllerAbstract
{
    public function __construct(
        private readonly AppPublicHtmlController $appPublicHtmlController,
        private readonly UserService $userService,
        private readonly ArticleService $articleService,
        private readonly FeedService $feedService,
        private readonly AlbumService $albumService,
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
                    isSuccess: false,
                    message: $localisationUtil->getValue('authenticationPasswordResetLinkError'),
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
     * Endpoint: /album/{albumSlug}
     * Method: GET
     *
     * @param string $albumSlug
     * @param Request $request
     * @return Response
     */
    protected function getAlbumPage(string $albumSlug, Request $request): Response
    {
        $album = $this->albumService->getAlbumForSlug($albumSlug);
        if (!$album) {
            $slug = $this->albumService->getAlbumSlugForSlug($albumSlug);
            if ($slug) {
                Response::createPermanentRedirectResponse(
                    url: UrlBuilderUtil::buildPublicAlbumUrl(
                        slug: $slug->slug,
                    ),
                );
            }

            return Response::createNotFoundResponse($request);
        }

        if (!$request->session?->isAdmin() && !$album->status->isPublic()) {
            return Response::createNotFoundResponse($request);
        }

        return Response::createHtmlResponse(
            template: 'core/frontend/public/album/' . $album->template->getTemplate(),
            responseData: new HtmlResponseData(
                request: $request,
                pageTitle: $album->titleHtml,
                pageDescription: $album->buildDescription(),
                siteImagePath: $album->mainMedia->getPathWithNameMedium(),
                album: $album,
            ),
        );
    }

    /**
     * Endpoint: /json-feed
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getJsonFeed(Request $request): Response
    {
        $feedItems = $this->articleService->getFeedItemsForArticles(
            articleType: ArticleType::Blog,
            languageIsoCode: $request->siteLanguage,
            maxItems: 20,
        );

        $json = $this->feedService->buildJsonFeed(
            localisationUtil: Core::getLocalisationUtil(Core::getDefaultLanguage()),
            feedItems: $feedItems,
        );

        return Response::createSuccessJsonResponse($json);
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
        $feedItems = $this->articleService->getFeedItemsForArticles(
            articleType: ArticleType::Blog,
            languageIsoCode: $request->siteLanguage,
            maxItems: 20,
        );

        $xml = $this->feedService->buildRss(
            localisationUtil: Core::getLocalisationUtil(Core::getDefaultLanguage()),
            feedItems: $feedItems,
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
        $feedItems = $this->articleService->getFeedItemsForArticles();
        $xml = $this->feedService->buildSitemap(
            feedItems: $feedItems,
        );

        return Response::createSuccessXmlResponse($xml);
    }

    private function buildHomepageResponse(
        Request $request,
        ?Feedback $feedback = null,
    ): Response {
        return $this->appPublicHtmlController->buildHomepageResponse(
            request: $request,
            feedback: $feedback,
        );
    }
}
