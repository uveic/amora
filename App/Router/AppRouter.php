<?php

namespace Amora\App\Router;

use Amora\Core\Router\Router;
use Exception;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;

class AppRouter
{
    const PUBLIC_HTML_CONTROLLER_ACTIONS = [];
    const PUBLIC_API_CONTROLLER_ACTIONS = [];

    const AUTHORISED_HTML_CONTROLLER_ACTIONS = [];
    const AUTHORISED_API_CONTROLLER_ACTIONS = [];

    const BACKOFFICE_HTML_CONTROLLER_ACTIONS = [];
    const BACKOFFICE_API_CONTROLLER_ACTIONS = [];

    public static function getReservedPaths(): array
    {
        return array_merge(
            Router::getReservedPaths(),
            array_keys(self::PUBLIC_HTML_CONTROLLER_ACTIONS),
            array_keys(self::PUBLIC_API_CONTROLLER_ACTIONS),
            array_keys(self::AUTHORISED_HTML_CONTROLLER_ACTIONS),
            array_keys(self::AUTHORISED_API_CONTROLLER_ACTIONS),
            array_keys(self::BACKOFFICE_HTML_CONTROLLER_ACTIONS),
            array_keys(self::BACKOFFICE_API_CONTROLLER_ACTIONS),
        );
    }

    public static function getPublicReservedPaths(): array
    {
        return [];
    }

    public static function getApiActions(): array
    {
        return array_merge(
            Router::getApiActions(),
            self::PUBLIC_API_CONTROLLER_ACTIONS,
            self::AUTHORISED_API_CONTROLLER_ACTIONS,
            self::BACKOFFICE_API_CONTROLLER_ACTIONS,
        );
    }

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
