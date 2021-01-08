<?php

namespace uve\router;

use uve\core\model\Request;
use uve\core\model\Response;
use uve\core\model\response\HtmlResponseData;
use uve\core\module\action\service\ActionService;
use uve\core\module\article\service\ImageService;
use uve\core\module\user\service\SessionService;
use uve\core\util\StringUtil;

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
     * Endpoint: /logout
     * Method: GET
     *
     * @param Request $request
     * @return Response
     */
    protected function logout(Request $request): Response
    {
        $this->sessionService->logout($request->getSession());
        $baseLinkUrl = StringUtil::getBaseLinkUrl($request->getSiteLanguage());
        return Response::createRedirectResponse($baseLinkUrl);
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
        return Response::createFrontendPrivateHtmlResponse(
            'account',
            new HtmlResponseData($request, 'User Account')
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
        return Response::createFrontendPrivateHtmlResponse(
            'account',
            new HtmlResponseData($request, 'User Account Settings')
        );
    }
}
