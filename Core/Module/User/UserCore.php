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
        return self::getInstance(
            className: 'UserDataLayer',
            factory: static function () {
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserStatus.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/User.php';
                require_once self::getPathRoot() . '/Core/Module/User/DataLayer/UserDataLayer.php';
                return new UserDataLayer(
                    db: self::getDb(),
                    logger: self::getUserLogger(),
                );
            },
        );
    }

    public static function getUserService(): UserService
    {
        return self::getInstance(
            className: 'UserService',
            factory: static function () {
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserJourneyStatus.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserRole.php';
                require_once self::getPathRoot() . '/App/Value/AppUserRole.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserStatus.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/VerificationType.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserActionType.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/UserAction.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/UserVerification.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/UserRegistrationRequest.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/User.php';
                require_once self::getPathRoot() . '/Core/Module/User/Service/UserService.php';
                return new UserService(
                    logger: self::getUserLogger(),
                    userDataLayer: self::getUserDataLayer(),
                    sessionService: self::getSessionService(),
                    userMailService: self::getUserMailService(),
                );
            },
        );
    }

    public static function getSessionDataLayer(): SessionDataLayer
    {
        return self::getInstance(
            className: 'SessionDataLayer',
            factory: static function () {
                require_once self::getPathRoot() . '/Core/Module/User/Model/Session.php';
                require_once self::getPathRoot() . '/Core/Module/User/DataLayer/UserDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserStatus.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/User.php';
                require_once self::getPathRoot() . '/Core/Module/User/DataLayer/SessionDataLayer.php';
                return new SessionDataLayer(
                    db: self::getDb(),
                    logger: self::getUserLogger(),
                );
            },
        );
    }

    public static function getSessionService(): SessionService
    {
        return self::getInstance(
            className: 'SessionService',
            factory: static function () {
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserJourneyStatus.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserRole.php';
                require_once self::getPathRoot() . '/App/Value/AppUserRole.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/Session.php';
                require_once self::getPathRoot() . '/Core/Entity/Util/UserAgentInfo.php';
                require_once self::getPathRoot() . '/Core/Util/UserAgentParserUtil.php';
                require_once self::getPathRoot() . '/Core/Module/User/Service/SessionService.php';
                return new SessionService(
                    sessionIdCookieName: Core::getConfig()->sessionIdCookieName,
                    sessionIdCookieValidForSeconds: Core::getConfig()->sessionIdCookieValidForSeconds,
                    dataLayer:self::getSessionDataLayer(),
                );
            },
        );
    }

    public static function getUserMailService(): UserMailService
    {
        return self::getInstance(
            className: 'UserMailService',
            factory: static function () {
                require_once self::getPathRoot() . '/Core/Module/User/Value/VerificationType.php';
                require_once self::getPathRoot() . '/Core/Module/User/Model/UserVerification.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Value/MailerTemplate.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/VerificationType.php';
                require_once self::getPathRoot() . '/Core/Module/User/Service/UserMailService.php';
                return new UserMailService(
                    userDataLayer: self::getUserDataLayer(),
                    mailerService: MailerCore::getMailerService(),
                );
            },
        );
    }
}
