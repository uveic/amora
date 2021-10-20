<?php

namespace Amora\App\Router;

use Amora\Core\Core;
use Exception;
use Amora\Core\Module\Action\ActionLoggerCore;
use Amora\Core\Module\User\UserCore;

class AppRouterCore extends Core
{
    /**
     * @return AppPublicHtmlController
     * @throws Exception
     */
    public static function getAppPublicHtmlController(): AppPublicHtmlController
    {
        return self::getInstance(
            'AppPublicHtmlController',
            function () {
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppPublicHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppPublicHtmlController.php';
                return new AppPublicHtmlController();
            },
            true
        );
    }

    /**
     * @return AppAuthorisedHtmlController
     * @throws Exception
     */
    public static function getAppAuthorisedHtmlController(): AppAuthorisedHtmlController
    {
        return self::getInstance(
            'AppAuthorisedHtmlController',
            function () {
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppAuthorisedHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppAuthorisedHtmlController.php';
                return new AppAuthorisedHtmlController();
            },
            true
        );
    }

    /**
     * @return AppBackofficeHtmlController
     * @throws Exception
     */
    public static function getAppBackofficeHtmlController(): AppBackofficeHtmlController
    {
        return self::getInstance(
            'AppBackofficeHtmlController',
            function () {
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppBackofficeHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppBackofficeHtmlController.php';
                return new AppBackofficeHtmlController();
            },
            true
        );
    }

    /**
     * @return AppBackofficeApiController
     * @throws Exception
     */
    public static function getAppBackofficeApiController(): AppBackofficeApiController
    {
        return self::getInstance(
            'AppBackofficeApiController',
            function () {
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppBackofficeApiControllerAbstract.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppBackofficeApiController.php';
                return new AppBackofficeApiController();
            },
            true
        );
    }

    /**
     * @return AppAuthorisedApiController
     * @throws Exception
     */
    public static function getAppAuthorisedApiController(): AppAuthorisedApiController
    {
        return self::getInstance(
            'AppAuthorisedApiController',
            function () {
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppAuthorisedApiControllerAbstract.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppAuthorisedApiController.php';
                return new AppAuthorisedApiController();
            },
            true
        );
    }

    /**
     * @return AppPublicApiController
     * @throws Exception
     */
    public static function getAppPublicApiController(): AppPublicApiController
    {
        return self::getInstance(
            'AppPublicApiController',
            function () {
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppPublicApiControllerAbstract.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppPublicApiController.php';
                return new AppPublicApiController();
            },
            true
        );
    }
}
