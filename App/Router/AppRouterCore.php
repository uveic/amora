<?php

namespace Amora\App\Router;

use Amora\Core\Core;
use Exception;

class AppRouterCore extends Core
{
    /**
     * @return AppPublicHtmlController
     * @throws Exception
     */
    public static function getAppPublicHtmlController(): AppPublicHtmlController
    {
        return self::getInstance(
            className: 'AppPublicHtmlController',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppPublicHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppPublicHtmlController.php';
                return new AppPublicHtmlController();
            },
        );
    }

    /**
     * @return AppAuthorisedHtmlController
     * @throws Exception
     */
    public static function getAppAuthorisedHtmlController(): AppAuthorisedHtmlController
    {
        return self::getInstance(
            className: 'AppAuthorisedHtmlController',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppAuthorisedHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppAuthorisedHtmlController.php';
                return new AppAuthorisedHtmlController();
            },
        );
    }

    /**
     * @return AppBackofficeHtmlController
     * @throws Exception
     */
    public static function getAppBackofficeHtmlController(): AppBackofficeHtmlController
    {
        return self::getInstance(
            className: 'AppBackofficeHtmlController',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppBackofficeHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppBackofficeHtmlController.php';
                return new AppBackofficeHtmlController();
            },
        );
    }

    /**
     * @return AppBackofficeApiController
     * @throws Exception
     */
    public static function getAppBackofficeApiController(): AppBackofficeApiController
    {
        return self::getInstance(
            className: 'AppBackofficeApiController',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppBackofficeApiControllerAbstract.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppBackofficeApiController.php';
                return new AppBackofficeApiController();
            },
        );
    }

    /**
     * @return AppAuthorisedApiController
     * @throws Exception
     */
    public static function getAppAuthorisedApiController(): AppAuthorisedApiController
    {
        return self::getInstance(
            className: 'AppAuthorisedApiController',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppAuthorisedApiControllerAbstract.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppAuthorisedApiController.php';
                return new AppAuthorisedApiController();
            },
        );
    }

    /**
     * @return AppPublicApiController
     * @throws Exception
     */
    public static function getAppPublicApiController(): AppPublicApiController
    {
        return self::getInstance(
            className: 'AppPublicApiController',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppPublicApiControllerAbstract.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppPublicApiController.php';
                return new AppPublicApiController();
            },
        );
    }
}
