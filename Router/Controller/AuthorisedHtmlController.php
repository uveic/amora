<?php

namespace Amora\Router;

use Amora\Core\Core;
use Amora\Core\Model\Request;
use Amora\Core\Model\Response;
use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Module\Action\Service\ActionService;
use Amora\Core\Module\Article\Service\ImageService;
use Amora\Core\Module\User\Service\SessionService;
use Amora\Core\Util\UrlBuilderUtil;

final class AuthorisedHtmlController extends AuthorisedHtmlControllerAbstract
{
    private SessionService $sessionService;
    private ImageService $imageService;
    private ActionService $actionService;

    public function __construct(
        SessionService $sessionService,
        ImageService $imageService,
        ActionService $actionService
    ) {
        $this->sessionService = $sessionService;
        $this->imageService = $imageService;
        $this->actionService = $actionService;

        parent::__construct();
    }

    protected function authenticate(Request $request): bool
    {
        $session = $request->getSession();
        $this->actionService->logAction($request, $session);

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
     */
    protected function getAppDashboardHtml(Request $request): Response
    {
        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());
        return Response::createFrontendPrivateHtmlResponse(
            'dashboard',
            new HtmlResponseData(
                request: $request,
                pageTitle: $localisationUtil->getValue('navDashboard')
            )
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
        $this->sessionService->logout($request->getSession());
        return Response::createRedirectResponse(
            UrlBuilderUtil::getPublicHomepageUrl($request->getSiteLanguage())
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
        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());
        return Response::createFrontendPrivateHtmlResponse(
            'account',
            new HtmlResponseData(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalUserAccount')
            )
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
        $localisationUtil = Core::getLocalisationUtil($request->getSiteLanguage());
        return Response::createFrontendPrivateHtmlResponse(
            'account',
            new HtmlResponseData(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalUserAccount')
            )
        );
    }
}
