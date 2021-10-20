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
        return true;
    }
}
