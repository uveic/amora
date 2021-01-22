<?php

namespace uve\core\module\user;

use uve\core\Core;
use uve\core\database\MySqlDb;
use uve\core\Logger;
use uve\core\module\mailer\MailerCore;
use uve\core\module\user\datalayer\SessionDataLayer;
use uve\core\module\user\datalayer\UserDataLayer;
use uve\core\module\user\service\UserMailService;
use uve\core\module\user\service\SessionService;
use uve\core\module\user\service\UserService;

class UserCore extends Core
{

    public static function getDb(): MySqlDb
    {
        return self::getCoreDb();
    }

    public static function getUserLogger(): Logger
    {
        return self::getLogger('user_module_logger');
    }

    public static function getUserDataLayer(): UserDataLayer
    {
        $db = self::getDb();
        $logger = self::getUserLogger();

        return self::getInstance(
            'UserDataLayer',
            function () use ($db, $logger) {
                require_once self::getPathRoot() . '/core/module/user/model/User.php';
                require_once self::getPathRoot() . '/core/module/user/datalayer/UserDataLayer.php';
                return new UserDataLayer($db, $logger);
            },
            true
        );
    }

    public static function getUserService(): UserService
    {
        $logger = self::getUserLogger();
        $userDataLayer = self::getUserDataLayer();
        $sessionService = self::getSessionService();
        $userMailService = self::getUserMailService();

        return self::getInstance(
            'UserService',
            function () use ($logger, $userDataLayer, $sessionService, $userMailService) {
                require_once Core::getPathRoot() . '/core/module/user/value/VerificationType.php';
                require_once Core::getPathRoot() . '/core/module/user/value/UserRole.php';
                require_once Core::getPathRoot() . '/core/module/user/model/UserVerification.php';
                require_once Core::getPathRoot() . '/core/module/user/model/UserRegistrationRequest.php';
                require_once self::getPathRoot() . '/core/module/user/model/User.php';
                require_once self::getPathRoot() . '/core/module/user/service/UserService.php';
                return new UserService($logger, $userDataLayer, $sessionService, $userMailService);
            },
            true
        );
    }

    public static function getSessionDataLayer(): SessionDataLayer
    {
        $db = self::getDb();
        $logger = self::getUserLogger();
        $userDataLayer = self::getUserDataLayer();

        return self::getInstance(
            'SessionDataLayer',
            function () use ($db, $logger, $userDataLayer) {
                require_once self::getPathRoot() . '/core/module/user/model/Session.php';
                require_once self::getPathRoot() . '/core/module/user/datalayer/SessionDataLayer.php';
                return new SessionDataLayer($db, $logger, $userDataLayer);
            },
            true
        );
    }

    public static function getSessionService(): SessionService
    {
        $dataLayer = self::getSessionDataLayer();
        $logger = self::getUserLogger();

        return self::getInstance(
            'SessionService',
            function () use ($dataLayer, $logger) {
                require_once self::getPathRoot() . '/core/module/user/model/Session.php';
                require_once self::getPathRoot() . '/core/model/util/UserAgentInfo.php';
                require_once self::getPathRoot() . '/core/util/UserAgentParserUtil.php';
                require_once self::getPathRoot() . '/core/module/user/value/UserRole.php';
                require_once self::getPathRoot() . '/core/module/user/service/SessionService.php';
                return new SessionService($dataLayer, $logger);
            },
            true
        );
    }

    public static function getUserMailService(): UserMailService
    {
        $logger = self::getUserLogger();
        $dataLayer = self::getUserDataLayer();
        $mailerService = MailerCore::getMailerService();

        return self::getInstance(
            'UserMailService',
            function () use ($dataLayer, $logger, $mailerService) {
                require_once self::getPathRoot() . '/core/module/user/value/VerificationType.php';
                require_once self::getPathRoot() . '/core/module/user/model/UserVerification.php';
                require_once self::getPathRoot() . '/core/module/mailer/value/MailerTemplate.php';
                require_once self::getPathRoot() . '/core/module/user/value/VerificationType.php';
                require_once self::getPathRoot() . '/core/module/user/service/UserMailService.php';
                return new UserMailService($logger, $dataLayer, $mailerService);
            },
            true
        );
    }
}
