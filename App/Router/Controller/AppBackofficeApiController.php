<?php

namespace Amora\App\Router;

use Amora\Core\Entity\Request;

final readonly class AppBackofficeApiController extends AppBackofficeApiControllerAbstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function authenticate(Request $request): bool
    {
        return $request->session?->isAdmin() ?? false;
    }
}
