<?php

namespace Amora\Core\Module\mailer;

use Amora\Core\Core;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Mailer\App\MailerApp;
use Amora\Core\Module\Mailer\App\Api\ApiClientAbstract;
use Amora\Core\Module\Mailer\App\Api\RequestBuilderAbstract;
use Amora\Core\Module\Mailer\App\Api\Sendgrid\ApiClient;
use Amora\Core\Module\Mailer\App\Api\Sendgrid\RequestBuilder;
use Amora\Core\Module\Mailer\DataLayer\MailerDataLayer;
use Amora\Core\Module\Mailer\Service\MailerService;

class MailerCore extends Core
{
    public static function getDb(): MySqlDb
    {
        return self::getMailerDb();
    }

    public static function getMailerLogger(): Logger
    {
        return self::getLogger();
    }

    public static function getMailerDataLayer(): MailerDataLayer
    {
        return self::getInstance(
            className: 'MailerDataLayer',
            factory: function () {
                $db = self::getDb();

                require_once self::getPathRoot() . '/App/Value/Mailer/AppMailerTemplate.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Model/MailerItem.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Model/MailerLogItem.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Value/MailerTemplate.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/DataLayer/MailerDataLayer.php';

                return new MailerDataLayer($db);
            },
            isSingleton: true,
        );
    }

    public static function getMailerService(): MailerService
    {
        return self::getInstance(
            className: 'MailerService',
            factory: function () {
                require_once self::getPathRoot() . '/App/Value/Mailer/AppMailerTemplate.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Model/MailerItem.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Model/MailerLogItem.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Value/MailerTemplate.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Service/MailerService.php';
                return new MailerService(
                    mailerDataLayer: self::getMailerDataLayer(),
                    mailerApp: self::getMailerApp(),
                    sendMailSynchronously: Core::getConfig()->mailer->sendEmailSynchronously,
                );
            },
            isSingleton: true,
        );
    }

    public static function getSendGridRequestBuilder(): RequestBuilderAbstract
    {
        return self::getInstance(
            className: 'SendGridRequestBuilder',
            factory: function () {
                $logger = self::getMailerLogger();
                $mailerConfig = self::getConfig()->mailer;

                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/RequestBuilderAbstract.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/Sendgrid/RequestBuilder.php';
                return new RequestBuilder(
                    logger: $logger,
                    fromEmail: $mailerConfig->from->email,
                    fromName: $mailerConfig->from->name,
                    replyToEmail: $mailerConfig->replyTo->email,
                    replyToName: $mailerConfig->replyTo->name,
                );
            },
            isSingleton: true,
        );
    }

    public static function getMailerApp(bool $isPersistent = true): MailerApp
    {
        return self::getInstance(
            className: 'MailerApp',
            factory: function () use($isPersistent) {
                require_once self::getPathRoot() . '/Core/Module/Mailer/Entity/Email.php';
                require_once self::getPathRoot() . '/Core/App/LockManager.php';
                require_once self::getPathRoot() . '/Core/App/App.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/MailerApp.php';
                return new MailerApp(
                    logger: self::getMailerLogger(),
                    dataLayer: self::getMailerDataLayer(),
                    apiClient: self::getSendGridMailerApiClient(),
                    requestBuilder: self::getSendGridRequestBuilder(),
                    isPersistent: $isPersistent,
                );
            },
            isSingleton: false,
        );
    }

    public static function getSendGridMailerApiClient(): ApiClientAbstract
    {
        return self::getInstance(
            className: 'ApiClient',
            factory: function () {
                $logger = self::getMailerLogger();
                $mailerConfig = self::getConfig()->mailer;

                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/ApiClientAbstract.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/Sendgrid/ApiClient.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/ApiResponse.php';
                return new ApiClient(
                    logger: $logger,
                    baseApiUrl: $mailerConfig->sendGrid->baseApiUrl,
                    apiKey: $mailerConfig->sendGrid->apiKey,
                );
            },
            isSingleton: false,
        );
    }
}
