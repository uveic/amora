<?php

namespace Amora\App\Router;

use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Module\User\Service\SessionService;
use Amora\Core\Util\UrlBuilderUtil;

final readonly class AppAuthorisedHtmlController extends AppAuthorisedHtmlControllerAbstract
{
    public function __construct(
        private SessionService $sessionService,
    ) {
        parent::__construct();
    }

    protected function authenticate(Request $request): bool
    {
        if (!$request->session?->isAuthenticated()) {
            return false;
        }

        return $this->sessionService->updateSessionExpiryDateAndValidUntil(
            sid: $request->session->sessionId,
            sessionId: $request->session->id,
        );
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
            url: UrlBuilderUtil::buildBackofficeDashboardUrl(
                $request->siteLanguage,
            ),
        );
    }
}
