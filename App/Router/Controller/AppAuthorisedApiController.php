<?php

namespace Amora\App\Router;

use Amora\Core\Entity\Request;

final class AppAuthorisedApiController extends AppAuthorisedApiControllerAbstract
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
}
