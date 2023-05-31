<?php

namespace Amora\Core\Router;

use Amora\Core\Module\Analytics\AnalyticsCore;
use Exception;
use Amora\Core\Core;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\User\UserCore;

class RouterCore extends Core
{
    private static function getRouterLogger(): Logger
    {
        return self::getLogger();
    }

    /**
     * @return PublicHtmlController
     * @throws Exception
     */
    public static function getPublicHtmlController(): PublicHtmlController
    {
        return self::getInstance(
            className: 'PublicHtmlController',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Entity/Response/HtmlHomepageResponseData.php';
                require_once self::getPathRoot() . '/Core/Entity/Response/HtmlResponseData.php';
                require_once self::getPathRoot() . '/Core/Entity/Response/Feedback.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Entity/SitemapItem.php';
                require_once self::getPathRoot() . '/Core/Util/Helper/ArticleHtmlGenerator.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/PublicHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/PublicHtmlController.php';
                return new PublicHtmlController(
                    userService:  UserCore::getUserService(),
                    articleService:  ArticleCore::getArticleService(),
                    xmlService:  ArticleCore::getXmlService(),
                );
            },
        );
    }

    /**
     * @return AuthorisedHtmlController
     * @throws Exception
     */
    public static function getAuthorisedHtmlController(): AuthorisedHtmlController
    {
        return self::getInstance(
            className: 'AuthorisedHtmlController',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Util/CsvWriterUtil.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AuthorisedHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AuthorisedHtmlController.php';
                return new AuthorisedHtmlController(
                    sessionService: UserCore::getSessionService(),
                );
            },
        );
    }

    /**
     * @return BackofficeHtmlController
     * @throws Exception
     */
    public static function getBackofficeHtmlController(): BackofficeHtmlController
    {
        return self::getInstance(
            className: 'BackofficeHtmlController',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Util/Helper/ArticleHtmlGenerator.php';
                require_once self::getPathRoot() . '/Core/Util/Helper/UserHtmlGenerator.php';
                require_once self::getPathRoot() . '/Core/Entity/Response/HtmlResponseData.php';
                require_once self::getPathRoot() . '/Core/Entity/Response/HtmlResponseDataAdmin.php';
                require_once self::getPathRoot() . '/Core/Entity/Response/HtmlResponseDataAnalytics.php';
                require_once self::getPathRoot() . '/Core/Entity/Util/DashboardCount.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/BackofficeHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/BackofficeHtmlController.php';
                return new BackofficeHtmlController(
                    userService:  UserCore::getUserService(),
                    articleService:  ArticleCore::getArticleService(),
                    mediaService:  ArticleCore::getMediaService(),
                    analyticsService: AnalyticsCore::getAnalyticsService(),
                );
            },
        );
    }

    /**
     * @return BackofficeApiController
     * @throws Exception
     */
    public static function getBackofficeApiController(): BackofficeApiController
    {
        return self::getInstance(
            className: 'BackofficeApiController',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Entity/Response/Feedback.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/BackofficeApiControllerAbstract.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/BackofficeApiController.php';
                return new BackofficeApiController(
                    logger: self::getRouterLogger(),
                    userService: UserCore::getUserService(),
                    articleService: ArticleCore::getArticleService(),
                    tagService: ArticleCore::getTagService(),
                    mediaService: ArticleCore::getMediaService(),
                );
            },
        );
    }

    /**
     * @return AuthorisedApiController
     * @throws Exception
     */
    public static function getAuthorisedApiController(): AuthorisedApiController
    {
        return self::getInstance(
            className: 'AuthorisedApiController',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Entity/Response/Feedback.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AuthorisedApiControllerAbstract.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AuthorisedApiController.php';
                return new AuthorisedApiController(
                    mediaService: ArticleCore::getMediaService(),
                    userService:  UserCore::getUserService(),
                    userMailService:  UserCore::getUserMailService(),
                );
            },
        );
    }

    /**
     * @return PublicApiController
     * @throws Exception
     */
    public static function getPublicApiController(): PublicApiController
    {
        return self::getInstance(
            className: 'PublicApiController',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Util/Helper/ArticleHtmlGenerator.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/PublicApiControllerAbstract.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/PublicApiController.php';
                return new PublicApiController(
                    logger: self::getRouterLogger(),
                    userService: UserCore::getUserService(),
                    sessionService: UserCore::getSessionService(),
                    mailService: UserCore::getUserMailService(),
                    articleService: ArticleCore::getArticleService(),
                );
            },
        );
    }
}
