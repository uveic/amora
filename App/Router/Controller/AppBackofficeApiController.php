<?php

namespace Amora\App\Router;

use Amora\Core\Entity\Request;

final class AppBackofficeApiController extends AppBackofficeApiControllerAbstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function authenticate(Request $request): bool
    {
        if (!$request->session || !$request->session->isAdmin()) {
            return false;
        }

        return true;
    }
}
