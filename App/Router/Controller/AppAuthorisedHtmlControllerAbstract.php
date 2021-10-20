<?php

namespace Amora\App\Router;

use Amora\Core\Core;
use Amora\Core\Model\Request;
use Amora\Core\Model\Response;
use Amora\Core\Router\AbstractController;
use Amora\Core\Router\RouterCore;
use Amora\Core\Util\StringUtil;
use Throwable;

abstract class AppAuthorisedHtmlControllerAbstract extends AbstractController
{
    public function __construct()
    {

    }

    abstract protected function authenticate(Request $request): bool;
   
    public function route(Request $request): ?Response
    {
        $auth = $this->authenticate($request);
        if ($auth !== true) {
            return Response::createUnauthorisedRedirectLoginResponse($request->getSiteLanguage());
        }

        $path = $request->getPath();
        $pathParts = explode('/', $path);
        $method = $request->getMethod();

        return null;
    }
}
