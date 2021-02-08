<?php

namespace uve\router;

use uve\core\Core;
use uve\core\model\response\HtmlResponseData;
use uve\core\model\response\UserFeedback;
use uve\core\module\action\service\ActionService;
use uve\core\module\article\service\ArticleService;
use uve\core\module\user\service\UserService;
use uve\core\module\user\service\SessionService;
use uve\core\model\Request;
use uve\core\model\Response;
use uve\core\util\UrlBuilderUtil;

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
        $isAdmin = $request->getSession() && $request->getSession()->isAdmin();
        $articles = $this->articleService->getArticlesForHome($isAdmin);
        return Response::createFrontendPublicHtmlResponse(
            'shared/home',
            new HtmlResponseData(
                $request,
                Core::getLocalisationUtil($request->getSiteLanguage())->getValue('siteTitle'),
                null,
                null,
                $articles,
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
                $request,
                null,
                null,
                null,
                [],
                new UserFeedback($isError, $message),
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
                    $request,
                    null,
                    null,
                    null,
                    null,
                    null,
                    new UserFeedback(true, $localisationUtil->getValue('authenticationPasswordResetLinkError'))
                )
            );
        }

        $user = $this->userService->getUserForId($res->getUserId());
        return Response::createFrontendPublicHtmlResponse(
            'shared/password-reset',
            new HtmlResponseData(
                $request,
                $localisationUtil->getValue('navChangePassword'),
                null,
                null,
                null,
                null,
                $user->getValidationHash(),
                $user->getId()
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
