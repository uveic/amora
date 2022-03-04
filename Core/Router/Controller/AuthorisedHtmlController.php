<?php

namespace Amora\Core\Router;

use Amora\Core\Core;
use Amora\Core\Model\Request;
use Amora\Core\Model\Response;
use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Module\User\Service\SessionService;
use Amora\Core\Util\UrlBuilderUtil;
use Exception;

final class AuthorisedHtmlController extends AuthorisedHtmlControllerAbstract
{
    public function __construct(
        private SessionService $sessionService,
    ) {
        parent::__construct();
    }

    protected function authenticate(Request $request): bool
    {
        $session = $request->session;

        if (empty($session) || !$session->isAuthenticated()) {
            return false;
        }

        return true;
    }

    /**
     * Endpoint: /dashboard
     * Method: GET
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    protected function getAppDashboardHtml(Request $request): Response
    {
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);

        return Response::createFrontendPrivateHtmlResponse(
            template: 'dashboard',
            responseData: new HtmlResponseData(
                request: $request,
                pageTitle: $localisationUtil->getValue('navDashboard'),
            ),
        );
    }

    /**
     * Endpoint: /logout
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function logout(Request $request): Response
    {
        $this->sessionService->logout($request->session);
        return Response::createRedirectResponse(
            url: UrlBuilderUtil::buildPublicHomepageUrl($request->siteLanguage),
        );
    }

    /**
     * Endpoint: /account
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function getUserAccountHtml(Request $request): Response
    {
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createFrontendPrivateHtmlResponse(
            template: 'account',
            responseData: new HtmlResponseData(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalUserAccount'),
            ),
        );
    }

    /**
     * Endpoint: /account/settings
     * Method: GET
     *
     * @param string $settingsPage
     * @param Request $request
     * @return Response
     */
    protected function getUserAccountSettingsHtml(string $settingsPage, Request $request): Response
    {
        $localisationUtil = Core::getLocalisationUtil($request->siteLanguage);
        return Response::createFrontendPrivateHtmlResponse(
            template: 'account',
            responseData: new HtmlResponseData(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalUserAccount'),
            ),
        );
    }
}
