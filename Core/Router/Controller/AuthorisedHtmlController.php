<?php

namespace Amora\Core\Router;

use Amora\Core\Core;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Module\User\Service\SessionService;
use Amora\Core\Util\UrlBuilderUtil;

final class AuthorisedHtmlController extends AuthorisedHtmlControllerAbstract
{
    public function __construct(
        private readonly SessionService $sessionService,
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
        return Response::createHtmlResponse(
            template: 'core/private/account',
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
        return Response::createHtmlResponse(
            template: 'core/private/account',
            responseData: new HtmlResponseData(
                request: $request,
                pageTitle: $localisationUtil->getValue('globalUserAccount'),
            ),
        );
    }
}
