<?php

namespace Amora\Core\Router;

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
        $userService = UserCore::getUserService();
        $articleService = ArticleCore::getArticleService();
        $rssService = ArticleCore::getRssService();

        return self::getInstance(
            className: 'PublicHtmlController',
            factory: function () use (
                $userService,
                $articleService,
                $rssService,
            ) {
                require_once self::getPathRoot() . '/Core/Model/Response/HtmlHomepageResponseData.php';
                require_once self::getPathRoot() . '/Core/Model/Response/HtmlResponseData.php';
                require_once self::getPathRoot() . '/Core/Model/Response/UserFeedback.php';
                require_once self::getPathRoot() . '/Core/Util/Helper/ArticleEditHtmlGenerator.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/PublicHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/PublicHtmlController.php';
                return new PublicHtmlController(
                    $userService,
                    $articleService,
                    $rssService,
                );
            },
            isSingleton: true,
        );
    }

    /**
     * @return AuthorisedHtmlController
     * @throws Exception
     */
    public static function getAuthorisedHtmlController(): AuthorisedHtmlController
    {
        $sessionService = UserCore::getSessionService();

        return self::getInstance(
            className: 'AuthorisedHtmlController',
            factory: function () use (
                $sessionService,
            ) {
                require_once self::getPathRoot() . '/Core/Module/Article/Value/ArticleType.php';
                require_once self::getPathRoot() . '/Core/Util/CsvWriterUtil.php';
                require_once self::getPathRoot() . '/Core/Util/UrlBuilderUtil.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AuthorisedHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AuthorisedHtmlController.php';
                return new AuthorisedHtmlController(
                    $sessionService,
                );
            },
            isSingleton: true,
        );
    }

    /**
     * @return BackofficeHtmlController
     * @throws Exception
     */
    public static function getBackofficeHtmlController(): BackofficeHtmlController
    {
        $userService = UserCore::getUserService();
        $articleService = ArticleCore::getArticleService();
        $imageService = ArticleCore::getImageService();

        return self::getInstance(
            className: 'BackofficeHtmlController',
            factory: function () use (
                $userService,
                $articleService,
                $imageService,
            ) {
                require_once self::getPathRoot() . '/Core/Util/Helper/ArticleEditHtmlGenerator.php';
                require_once self::getPathRoot() . '/Core/Model/Response/HtmlResponseData.php';
                require_once self::getPathRoot() . '/Core/Model/Response/HtmlResponseDataAuthorised.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/BackofficeHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/BackofficeHtmlController.php';
                return new BackofficeHtmlController(
                    $userService,
                    $articleService,
                    $imageService,
                );
            },
            isSingleton: true,
        );
    }

    /**
     * @return BackofficeApiController
     * @throws Exception
     */
    public static function getBackofficeApiController(): BackofficeApiController
    {
        $logger = self::getRouterLogger();
        $userService = UserCore::getUserService();
        $articleService = ArticleCore::getArticleService();
        $tagService = ArticleCore::getTagService();

        return self::getInstance(
            className: 'BackofficeApiController',
            factory: function () use (
                $logger,
                $userService,
                $articleService,
                $tagService,
            ) {
                require_once self::getPathRoot() . '/Core/Model/Response/UserFeedback.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/BackofficeApiControllerAbstract.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/BackofficeApiController.php';
                return new BackofficeApiController(
                    $logger,
                    $userService,
                    $articleService,
                    $tagService,
                );
            },
            isSingleton: true,
        );
    }

    /**
     * @return AuthorisedApiController
     * @throws Exception
     */
    public static function getAuthorisedApiController(): AuthorisedApiController
    {
        $logger = self::getRouterLogger();
        $imageService = ArticleCore::getImageService();
        $userService = UserCore::getUserService();
        $userMailService = UserCore::getUserMailService();

        return self::getInstance(
            className: 'AuthorisedApiController',
            factory: function () use (
                $logger,
                $imageService,
                $userService,
                $userMailService,
            ) {
                require_once self::getPathRoot() . '/Core/Model/Response/UserFeedback.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AuthorisedApiControllerAbstract.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AuthorisedApiController.php';
                return new AuthorisedApiController(
                    $logger,
                    $imageService,
                    $userService,
                    $userMailService,
                );
            },
            isSingleton: true,
        );
    }

    /**
     * @return PublicApiController
     * @throws Exception
     */
    public static function getPublicApiController(): PublicApiController
    {
        $logger = self::getRouterLogger();
        $userService = UserCore::getUserService();
        $sessionService = UserCore::getSessionService();
        $mailService = UserCore::getUserMailService();
        $articleService = ArticleCore::getArticleService();

        return self::getInstance(
            className: 'PublicApiController',
            factory: function () use (
                $logger,
                $userService,
                $sessionService,
                $mailService,
                $articleService,
            ) {
                require_once self::getPathRoot() . '/Core/Util/Helper/ArticleEditHtmlGenerator.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/PublicApiControllerAbstract.php';
                require_once self::getPathRoot() . '/Core/Router/Controller/PublicApiController.php';
                return new PublicApiController(
                    $logger,
                    $userService,
                    $sessionService,
                    $mailService,
                    $articleService,
                );
            },
            isSingleton: true,
        );
    }
}
