<?php

namespace Amora\App\Router;

use Amora\Core\Core;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\User\UserCore;

class AppRouterCore extends Core
{
    public static function getAppPublicHtmlController(): AppPublicHtmlController
    {
        return self::getInstance(
            className: 'AppPublicHtmlController',
            factory: function () {
                require_once self::getPathRoot() . '/App/Entity/AppHtmlHomepageResponseData.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppPublicHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppPublicHtmlController.php';
                return new AppPublicHtmlController(
                    sessionService: UserCore::getSessionService(),
                    articleService: ArticleCore::getArticleService(),
                );
            },
        );
    }

    public static function getAppAuthorisedHtmlController(): AppAuthorisedHtmlController
    {
        return self::getInstance(
            className: 'AppAuthorisedHtmlController',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppAuthorisedHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppAuthorisedHtmlController.php';
                return new AppAuthorisedHtmlController(
                    sessionService: UserCore::getSessionService(),
                );
            },
        );
    }

    public static function getAppBackofficeHtmlController(): AppBackofficeHtmlController
    {
        return self::getInstance(
            className: 'AppBackofficeHtmlController',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppBackofficeHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/App/Router/Controller/AppBackofficeHtmlController.php';
                return new AppBackofficeHtmlController(
                    sessionService: UserCore::getSessionService(),
                );
            },
        );
    }

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
