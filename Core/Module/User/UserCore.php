<?php

namespace Amora\Core\Module\User;

use Amora\Core\Core;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Mailer\MailerCore;
use Amora\Core\Module\User\DataLayer\SessionDataLayer;
use Amora\Core\Module\User\DataLayer\UserDataLayer;
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
        return self::getLogger();
    }

    public static function getUserDataLayer(): UserDataLayer
    {
        $db = self::getDb();
        $logger = self::getUserLogger();

        return self::getInstance(
            className: 'UserDataLayer',
            factory: function () use ($db, $logger) {
                require_once self::getPathRoot() . '/Core/Module/User/Model/User.php';
                require_once self::getPathRoot() . '/Core/Module/User/DataLayer/UserDataLayer.php';
                return new UserDataLayer($db, $logger);
            },
            isSingleton: true,
        );
    }

    public static function getUserService(): UserService
    {
        $logger = self::getUserLogger();
        $userDataLayer = self::getUserDataLayer();
        $sessionService = self::getSessionService();
        $userMailService = self::getUserMailService();

        return self::getInstance(
            className: 'UserService',
            factory: function () use ($logger, $userDataLayer, $sessionService, $userMailService) {
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserJourneyStatus.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserRole.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/VerificationType.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/UserVerification.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/UserRegistrationRequest.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/User.php';
                require_once self::getPathRoot() . '/Core/Module/User/Service/UserService.php';
                return new UserService($logger, $userDataLayer, $sessionService, $userMailService);
            },
            isSingleton: true,
        );
    }

    public static function getSessionDataLayer(): SessionDataLayer
    {
        $db = self::getDb();
        $logger = self::getUserLogger();

        return self::getInstance(
            className: 'SessionDataLayer',
            factory: function () use ($db, $logger) {
                require_once self::getPathRoot() . '/Core/Module/User/Model/Session.php';
                require_once self::getPathRoot() . '/Core/Module/User/DataLayer/UserDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/User.php';
                require_once self::getPathRoot() . '/Core/Module/User/DataLayer/SessionDataLayer.php';
                return new SessionDataLayer($db, $logger);
            },
            isSingleton: true,
        );
    }

    public static function getSessionService(): SessionService
    {
        $dataLayer = self::getSessionDataLayer();

        return self::getInstance(
            className: 'SessionService',
            factory: function () use ($dataLayer) {
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserJourneyStatus.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserRole.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/Session.php';
                require_once self::getPathRoot() . '/Core/Entity/Util/UserAgentInfo.php';
                require_once self::getPathRoot() . '/Core/Util/UserAgentParserUtil.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserRole.php';
                require_once self::getPathRoot() . '/Core/Module/User/Service/SessionService.php';
                return new SessionService($dataLayer);
            },
            isSingleton: true,
        );
    }

    public static function getUserMailService(): UserMailService
    {
        $dataLayer = self::getUserDataLayer();
        $mailerService = MailerCore::getMailerService();

        return self::getInstance(
            className: 'UserMailService',
            factory: function () use ($dataLayer, $mailerService) {
                require_once self::getPathRoot() . '/Core/Module/User/Value/VerificationType.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/UserVerification.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Value/MailerTemplate.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/VerificationType.php';
                require_once self::getPathRoot() . '/Core/Module/User/Service/UserMailService.php';
                return new UserMailService($dataLayer, $mailerService);
            },
            isSingleton: true,
        );
    }
}
