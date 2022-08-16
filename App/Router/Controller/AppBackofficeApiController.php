<?php

namespace Amora\App\Router;

use Amora\Core\Model\Request;

final class AppBackofficeApiController extends AppBackofficeApiControllerAbstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function authenticate(Request $request): bool
    {
        $session = $request->session;
        if (empty($session) || !$session->isAuthenticated() || !$session->isAdmin()) {
            return false;
        }

        return true;
    }
}
