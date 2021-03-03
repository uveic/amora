<?php

namespace Amora\Router;

use Amora\Core\Core;
use Amora\Core\Model\Response\HtmlHomepageResponseData;
use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Model\Response\UserFeedback;
use Amora\Core\Module\Action\Service\ActionService;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Module\User\Service\SessionService;
use Amora\Core\Model\Request;
use Amora\Core\Model\Response;
use Amora\Core\Util\UrlBuilderUtil;

final class PublicHtmlController extends PublicHtmlControllerAbstract
{
    public function __construct(
        private SessionService $sessionService,
        private UserService $userService,
        private ArticleService $articleService,
        private ActionService $actionService
    ) {
        parent::__construct();
    }

    protected function authenticate(Request $request): bool
    {
        $this->actionService->logAction($request, $request->getSession());

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
        $homeArticles = $this->articleService->getArticlesForHome();
        $homepageArticle = $this->articleService->getHomepageArticle();
        return Response::createFrontendPublicHtmlResponse(
            'shared/home',
            new HtmlHomepageResponseData(
                request: $request,
                article: $homepageArticle,
                homeArticles: $homeArticles,
            )
        );
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
        $session = $request->getSession();
        $isAuthenticated = $session && $session->isAuthenticated();
        if ($isAuthenticated) {
            $baseLinkUrl = UrlBuilderUtil::getBaseLinkUrl($request->getSiteLanguage());
            return Response::createRedirectResponse($baseLinkUrl . '/backoffice/dashboard');
        }

        return Response::createFrontendPublicHtmlResponse(
            'shared/login',
            new HtmlResponseData(
                $request,
                Core::getLocalisationUtil($request->getSiteLanguage())->getValue('formLoginAction')
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
        $session = $request->getSession();
        $isAuthenticated = $session && $session->isAuthenticated();
        if ($isAuthenticated) {
            $baseLinkUrl = UrlBuilderUtil::getBaseLinkUrl($request->getSiteLanguage());
            return Response::createRedirectResponse($baseLinkUrl . '/backoffice/dashboard');
        }

        return Response::createFrontendPublicHtmlResponse(
            'shared/login-forgot',
            new HtmlResponseData(
                $request,
                Core::getLocalisationUtil($request->getSiteLanguage())->getValue('authenticationForgotPassword')
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
        $session = $request->getSession();
        $isAuthenticated = $session && $session->isAuthenticated();
        if ($isAuthenticated) {
            $baseLinkUrl = UrlBuilderUtil::getBaseLinkUrl($request->getSiteLanguage());
            return Response::createRedirectResponse($baseLinkUrl . '/backoffice/dashboard');
        }

        $isRegistrationEnabled = Core::getConfigValue('registrationEnabled');
        if (!$isRegistrationEnabled) {
            $isInvitationEnabled = Core::getConfigValue('invitationEnabled');
            if ($isInvitationEnabled) {
                $baseLinkUrl = UrlBuilderUtil::getBaseLinkUrl($request->getSiteLanguage());
                return Response::createRedirectResponse($baseLinkUrl . '/invite-request');
            }

            return Response::createRedirectResponse(
                UrlBuilderUtil::getBaseLinkUrl($request->getSiteLanguage())
            );
        }

        return Response::createFrontendPublicHtmlResponse(
            'shared/register',
            new HtmlResponseData(
                $request,
                Core::getLocalisationUtil($request->getSiteLanguage())->getValue('navSignUp')
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
        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());
        $res = $this->userService->validateEmailAddressVerificationPage($verificationIdentifier);
        $isError = !$res;
        $message = $res
            ? $localisationUtil->getValue('authenticationEmailVerified')
            : $localisationUtil->getValue('authenticationEmailVerifiedError');

        return Response::createFrontendPublicHtmlResponse(
            strtolower($request->getSiteLanguage()) . '/home',
            new HtmlResponseData(
                request: $request,
                userFeedback: new UserFeedback($isError, $message),
            )
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
        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());

        if (empty($res)) {
            return Response::createFrontendPublicHtmlResponse(
                strtolower($request->getSiteLanguage()) . '/home',
                new HtmlResponseData(
                    request: $request,
                    userFeedback: new UserFeedback(
                        true,
                        $localisationUtil->getValue('authenticationPasswordResetLinkError')
                    )
                )
            );
        }

        $user = $this->userService->getUserForId($res->getUserId());
        return Response::createFrontendPublicHtmlResponse(
            'shared/password-reset',
            new HtmlResponseData(
                request: $request,
                pageTitle: $localisationUtil->getValue('navChangePassword'),
                verificationHash: $user->getValidationHash(),
                forgotPasswordUserId: $user->getId()
            )
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
        $session = $request->getSession();
        $isAuthenticated = $session && $session->isAuthenticated();
        if ($isAuthenticated) {
            $baseLinkUrl = UrlBuilderUtil::getBaseLinkUrl($request->getSiteLanguage());
            return Response::createRedirectResponse($baseLinkUrl . '/backoffice/dashboard');
        }


        $isInvitationEnabled = Core::getConfigValue('invitationEnabled');
        if (!$isInvitationEnabled) {
            return Response::createRedirectResponse(
                UrlBuilderUtil::getBaseLinkUrl($request->getSiteLanguage())
            );
        }

        return Response::createFrontendPublicHtmlResponse(
            'shared/invite-request',
            new HtmlResponseData(
                $request,
                Core::getLocalisationUtil($request->getSiteLanguage())->getValue('navSignUp')
            )
        );
    }
}
