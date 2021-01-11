<?php

namespace uve\router;

use Exception;
use uve\core\Core;
use uve\core\Logger;
use uve\core\module\action\ActionLoggerCore;
use uve\core\module\article\ArticleCore;
use uve\core\module\user\UserCore;

class RouterCore extends Core
{
    private static function getRouterLogger(): Logger
    {
        return self::getLogger('router_app_logger');
    }

    /**
     * @return PublicHtmlController
     * @throws Exception
     */
    public static function getPublicHtmlController(): PublicHtmlController
    {
        $sessionService = UserCore::getSessionService();
        $userService = UserCore::getUserService();
        $articleService = ArticleCore::getArticleService();
        $actionService = ActionLoggerCore::getActionService();

        return self::getInstance(
            'PublicHtmlController',
            function () use (
                $sessionService,
                $userService,
                $articleService,
                $actionService
            ) {
                require_once self::getPathRoot() . '/core/model/response/UserFeedback.php';
                require_once self::getPathRoot() . '/router/controller/AbstractController.php';
                require_once self::getPathRoot() . '/router/controller/PublicHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/router/controller/PublicHtmlController.php';
                return new PublicHtmlController(
                    $sessionService,
                    $userService,
                    $articleService,
                    $actionService
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
        $imageService = ArticleCore::getImageService();
        $actionService = ActionLoggerCore::getActionService();

        return self::getInstance(
            'AuthorisedHtmlController',
            function () use (
                $sessionService,
                $imageService,
                $actionService
            ) {
                require_once self::getPathRoot() . '/core/module/user/service/UserService.php';
                require_once self::getPathRoot() . '/core/module/user/value/UserJourneyStatus.php';
                require_once self::getPathRoot() . '/router/controller/AbstractController.php';
                require_once self::getPathRoot() . '/router/controller/AuthorisedHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/router/controller/AuthorisedHtmlController.php';
                return new AuthorisedHtmlController(
                    $sessionService,
                    $imageService,
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
        $logger = self::getLogger('backofficeHtmlController');
        $actionService = ActionLoggerCore::getActionService();
        $sessionService = UserCore::getSessionService();
        $userService = UserCore::getUserService();
        $articleService = ArticleCore::getArticleService();
        $imageService = ArticleCore::getImageService();

        return self::getInstance(
            'BackofficeHtmlController',
            function () use (
                $logger,
                $actionService,
                $sessionService,
                $userService,
                $articleService,
                $imageService
            ) {
                require_once self::getPathRoot() . '/router/controller/AbstractController.php';
                require_once self::getPathRoot() . '/router/controller/BackofficeHtmlControllerAbstract.php';
                require_once self::getPathRoot() . '/router/controller/BackofficeHtmlController.php';
                return new BackofficeHtmlController(
                    $logger,
                    $actionService,
                    $sessionService,
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
        $logger = self::getLogger('backofficeApiController');
        $actionService = ActionLoggerCore::getActionService();
        $sessionService = UserCore::getSessionService();
        $userService = UserCore::getUserService();
        $articleService = ArticleCore::getArticleService();
        $imageService = ArticleCore::getImageService();

        return self::getInstance(
            'BackofficeApiController',
            function () use (
                $logger,
                $actionService,
                $sessionService,
                $userService,
                $articleService,
                $imageService
            ) {
                require_once self::getPathRoot() . '/core/model/response/UserFeedback.php';
                require_once self::getPathRoot() . '/core/module/user/value/UserJourneyStatus.php';
                require_once self::getPathRoot() . '/router/controller/AbstractController.php';
                require_once self::getPathRoot() . '/router/controller/BackofficeApiControllerAbstract.php';
                require_once self::getPathRoot() . '/router/controller/BackofficeApiController.php';
                return new BackofficeApiController(
                    $logger,
                    $actionService,
                    $sessionService,
                    $userService,
                    $articleService,
                    $imageService
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
        $sessionService = UserCore::getSessionService();
        $imageService = ArticleCore::getImageService();
        $articleService = ArticleCore::getArticleService();
        $userService = UserCore::getUserService();
        $userMailService = UserCore::getUserMailService();
        $actionService = ActionLoggerCore::getActionService();

        return self::getInstance(
            'AuthorisedApiController',
            function () use (
                $logger,
                $sessionService,
                $imageService,
                $articleService,
                $userService,
                $userMailService,
                $actionService
            ) {
                require_once self::getPathRoot() . '/core/model/response/UserFeedback.php';
                require_once self::getPathRoot() . '/core/module/user/value/UserJourneyStatus.php';
                require_once self::getPathRoot() . '/core/module/user/value/UserRole.php';
                require_once self::getPathRoot() . '/router/controller/AbstractController.php';
                require_once self::getPathRoot() . '/router/controller/AuthorisedApiControllerAbstract.php';
                require_once self::getPathRoot() . '/router/controller/AuthorisedApiController.php';
                return new AuthorisedApiController(
                    $logger,
                    $sessionService,
                    $imageService,
                    $articleService,
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
                require_once self::getPathRoot() . '/core/module/user/value/UserJourneyStatus.php';
                require_once self::getPathRoot() . '/router/controller/AbstractController.php';
                require_once self::getPathRoot() . '/router/controller/PublicApiControllerAbstract.php';
                require_once self::getPathRoot() . '/router/controller/PublicApiController.php';
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
