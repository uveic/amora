<?php

namespace Amora\App\Router;

use Amora\Core\Model\Request;

final class AppPublicApiController extends AppPublicApiControllerAbstract
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
