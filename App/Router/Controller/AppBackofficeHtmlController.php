<?php

namespace Amora\App\Router;

use Amora\Core\Entity\Request;
use Amora\Core\Module\User\Service\SessionService;

final class AppBackofficeHtmlController extends AppBackofficeHtmlControllerAbstract
{
    public function __construct(
        private readonly SessionService $sessionService,
    ) {
        parent::__construct();
    }

    protected function authenticate(Request $request): bool
    {
        if (!$request->session || !$request->session->isAdmin()) {
            return false;
        }

        return $this->sessionService->updateSessionExpiryDateAndValidUntil(
            sid: $request->session->sessionId,
            sessionId: $request->session->id,
        );
    }
}
