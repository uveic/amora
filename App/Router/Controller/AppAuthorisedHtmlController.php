<?php

namespace Amora\App\Router;

use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Util\UrlBuilderUtil;

final class AppAuthorisedHtmlController extends AppAuthorisedHtmlControllerAbstract
{
    public function __construct()
    {
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
     */
    protected function getAppDashboardHtml(Request $request): Response
    {
        return Response::createRedirectResponse(
            url: UrlBuilderUtil::buildBackofficeDashboardUrl($request->siteLanguage),
        );
    }
}
