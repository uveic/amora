<?php

namespace Amora\App\Router;

use Amora\Core\Model\Request;

final class AppAuthorisedApiController extends AppAuthorisedApiControllerAbstract
{

    protected function authenticate(Request $request): bool
    {
        return true;
    }
}
