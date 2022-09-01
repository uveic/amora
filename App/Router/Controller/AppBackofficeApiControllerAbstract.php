<?php

namespace Amora\App\Router;

use Amora\Core\Core;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Router\AbstractController;
use Amora\Core\Router\RouterCore;
use Amora\Core\Util\StringUtil;
use Throwable;

abstract class AppBackofficeApiControllerAbstract extends AbstractController
{
    public function __construct()
    {

    }

    abstract protected function authenticate(Request $request): bool;
   
    public function route(Request $request): ?Response
    {
        $auth = $this->authenticate($request);
        if ($auth !== true) {
            return Response::createUnauthorizedJsonResponse();
        }

        $path = $request->getPath();
        $pathParts = explode('/', $path);
        $method = $request->method;

        return null;
    }
}
