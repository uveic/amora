<?php

namespace Amora\App\Router;

use Exception;
use Amora\Core\Model\Request;
use Amora\Core\Model\Response;

class AppRouter
{
    const PUBLIC_HTML_CONTROLLER_ACTIONS = [];
    const PUBLIC_API_CONTROLLER_ACTIONS = [];

    const AUTHORISED_HTML_CONTROLLER_ACTIONS = [];
    const AUTHORISED_API_CONTROLLER_ACTIONS = [];

    const BACKOFFICE_HTML_CONTROLLER_ACTIONS = [];
    const BACKOFFICE_API_CONTROLLER_ACTIONS = [];

    /**
     * @param Request $request
     * @return Response|null
     * @throws Exception
     */
    public static function route(Request $request): ?Response
    {
        return null;
    }
}
