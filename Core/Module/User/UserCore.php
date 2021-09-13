<?php

namespace Amora\Core\Module\User;

use Amora\Core\Core;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Logger;
use Amora\Core\Module\Mailer\MailerCore;
use Amora\Core\Module\User\Datalayer\SessionDataLayer;
use Amora\Core\Module\User\Datalayer\UserDataLayer;
use Amora\Core\Module\User\Service\UserMailService;
use Amora\Core\Module\User\Service\SessionService;
use Amora\Core\Module\User\Service\UserService;

class UserCore extends Core
{

    public static function getDb(): MySqlDb
    {
        return self::getCoreDb();
    }

    public static function getUserLogger(): Logger
    {
        return self::getLogger('UserModuleLogger');
    }

    public static function getUserDataLayer(): UserDataLayer
    {
        $db = self::getDb();
        $logger = self::getUserLogger();

        return self::getInstance(
            'UserDataLayer',
            function () use ($db, $logger) {
                require_once self::getPathRoot() . '/Core/Module/User/Model/User.php';
                require_once self::getPathRoot() . '/Core/Module/User/Datalayer/UserDataLayer.php';
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
                require_once self::getPathRoot() . '/Core/Module/User/Value/VerificationType.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserRole.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/UserVerification.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/UserRegistrationRequest.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/User.php';
                require_once self::getPathRoot() . '/Core/Module/User/Service/UserService.php';
                return new UserService($logger, $userDataLayer, $sessionService, $userMailService);
            },
            true
        );
    }

    public static function getSessionDataLayer(): SessionDataLayer
    {
        $db = self::getDb();
        $logger = self::getUserLogger();

        return self::getInstance(
            'SessionDataLayer',
            function () use ($db, $logger) {
                require_once self::getPathRoot() . '/Core/Module/User/Model/Session.php';
                require_once self::getPathRoot() . '/Core/Module/User/Datalayer/UserDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/User.php';
                require_once self::getPathRoot() . '/Core/Module/User/Datalayer/SessionDataLayer.php';
                return new SessionDataLayer($db, $logger);
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
                require_once self::getPathRoot() . '/Core/Module/User/Model/Session.php';
                require_once self::getPathRoot() . '/Core/Model/Util/UserAgentInfo.php';
                require_once self::getPathRoot() . '/Core/Util/UserAgentParserUtil.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserRole.php';
                require_once self::getPathRoot() . '/Core/Module/User/Service/SessionService.php';
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
                require_once self::getPathRoot() . '/Core/Module/User/Value/VerificationType.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/UserVerification.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Value/MailerTemplate.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/VerificationType.php';
                require_once self::getPathRoot() . '/Core/Module/User/Service/UserMailService.php';
                return new UserMailService($logger, $dataLayer, $mailerService);
            },
            true
        );
    }
}
