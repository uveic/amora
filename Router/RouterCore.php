<?php

namespace Amora\Router;

use Exception;
use Amora\Core\Core;
use Amora\Core\Logger;
use Amora\Core\Module\Action\ActionLoggerCore;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\User\UserCore;

class RouterCore extends Core
{
    private static function getRouterLogger(): Logger
    {
        return self::getLogger('RouterAppLogger');
    }

    /**
     * @return PublicHtmlController
     * @throws Exception
     */
    public static function getPublicHtmlController(): PublicHtmlController
    {
        $userService = UserCore::getUserService();
        $articleService = ArticleCore::getArticleService();
        $actionService = ActionLoggerCore::getActionService();
        $rssService = ArticleCore::getRssService();

        return self::getInstance(
            'PublicHtmlController',
            function () use (
                $userService,
                $articleService,
                $actionService,
                $rssService,
            ) {
                require_once self::getPathRoot() . '/Core/Model/Response/HtmlHomepageResponseData.php';
                require_once self::getPathRoot() . '/Core/Model/Response/HtmlResponseData.php';
                require_once self::getPathRoot() . '/Core/Model/Response/UserFeedback.php';
                require_once self::getPathRoot() . '/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Router/Controller/PublicHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/Router/Controller/PublicHtmlController.php';
                return new PublicHtmlController(
                    $userService,
                    $articleService,
                    $actionService,
                    $rssService,
                );
            },
            true
        );
    }

    /**
     * @return AuthorisedHtmlController
     * @throws Exception
     */
    public static function getAuthorisedHtmlController(): AuthorisedHtmlController
    {
        $sessionService = UserCore::getSessionService();
        $actionService = ActionLoggerCore::getActionService();

        return self::getInstance(
            'AuthorisedHtmlController',
            function () use (
                $sessionService,
                $actionService
            ) {
                require_once self::getPathRoot() . '/Core/Module/User/Service/UserService.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserJourneyStatus.php';
                require_once self::getPathRoot() . '/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Router/Controller/AuthorisedHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/Router/Controller/AuthorisedHtmlController.php';
                return new AuthorisedHtmlController(
                    $sessionService,
                    $actionService
                );
            },
            true
        );
    }

    /**
     * @return BackofficeHtmlController
     * @throws Exception
     */
    public static function getBackofficeHtmlController(): BackofficeHtmlController
    {
        $actionService = ActionLoggerCore::getActionService();
        $userService = UserCore::getUserService();
        $articleService = ArticleCore::getArticleService();
        $imageService = ArticleCore::getImageService();

        return self::getInstance(
            'BackofficeHtmlController',
            function () use (
                $actionService,
                $userService,
                $articleService,
                $imageService
            ) {
                require_once self::getPathRoot() . '/Core/Util/Helper/ArticleEditHtmlGenerator.php';
                require_once self::getPathRoot() . '/Core/Model/Response/HtmlResponseData.php';
                require_once self::getPathRoot() . '/Core/Model/Response/HtmlResponseDataAuthorised.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserJourneyStatus.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/ArticleSectionType.php';
                require_once self::getPathRoot() . '/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Router/Controller/BackofficeHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/Router/Controller/BackofficeHtmlController.php';
                return new BackofficeHtmlController(
                    $actionService,
                    $userService,
                    $articleService,
                    $imageService
                );
            },
            true
        );
    }

    /**
     * @return BackofficeApiController
     * @throws Exception
     */
    public static function getBackofficeApiController(): BackofficeApiController
    {
        $logger = self::getRouterLogger();
        $actionService = ActionLoggerCore::getActionService();
        $userService = UserCore::getUserService();
        $articleService = ArticleCore::getArticleService();
        $tagService = ArticleCore::getTagService();

        return self::getInstance(
            'BackofficeApiController',
            function () use (
                $logger,
                $actionService,
                $userService,
                $articleService,
                $tagService,
            ) {
                require_once self::getPathRoot() . '/Core/Model/Response/UserFeedback.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserJourneyStatus.php';
                require_once self::getPathRoot() . '/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Router/Controller/BackofficeApiControllerAbstract.php';
                require_once self::getPathRoot() . '/Router/Controller/BackofficeApiController.php';
                return new BackofficeApiController(
                    $logger,
                    $actionService,
                    $userService,
                    $articleService,
                    $tagService
                );
            },
            true
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
        $actionService = ActionLoggerCore::getActionService();

        return self::getInstance(
            'AuthorisedApiController',
            function () use (
                $logger,
                $imageService,
                $userService,
                $userMailService,
                $actionService
            ) {
                require_once self::getPathRoot() . '/Core/Model/Response/UserFeedback.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserJourneyStatus.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserRole.php';
                require_once self::getPathRoot() . '/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Router/Controller/AuthorisedApiControllerAbstract.php';
                require_once self::getPathRoot() . '/Router/Controller/AuthorisedApiController.php';
                return new AuthorisedApiController(
                    $logger,
                    $imageService,
                    $userService,
                    $userMailService,
                    $actionService
                );
            },
            true
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
        $actionService = ActionLoggerCore::getActionService();

        return self::getInstance(
            'PublicApiController',
            function () use (
                $logger,
                $userService,
                $sessionService,
                $mailService,
                $actionService
            ) {
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserJourneyStatus.php';
                require_once self::getPathRoot() . '/Router/Controller/AbstractController.php';
                require_once self::getPathRoot() . '/Router/Controller/PublicApiControllerAbstract.php';
                require_once self::getPathRoot() . '/Router/Controller/PublicApiController.php';
                return new PublicApiController(
                    $logger,
                    $userService,
                    $sessionService,
                    $mailService,
                    $actionService
                );
            },
            true
        );
    }
}
