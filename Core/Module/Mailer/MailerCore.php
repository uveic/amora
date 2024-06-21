<?php

namespace Amora\Core\Module\mailer;

use Amora\Core\Config\MailerClient;
use Amora\Core\Core;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Mailer\App\MailerApp;
use Amora\Core\Module\Mailer\App\Api\ApiClientAbstract;
use Amora\Core\Module\Mailer\App\Api\RequestBuilderAbstract;
use Amora\Core\Module\Mailer\App\Api\Brevo\ApiClient as BrevoApiClient;
use Amora\Core\Module\Mailer\App\Api\Brevo\RequestBuilder as BrevoRequestBuilder;
use Amora\Core\Module\Mailer\App\Api\Sendgrid\ApiClient as SendGridApiClient;
use Amora\Core\Module\Mailer\App\Api\Sendgrid\RequestBuilder as SendGridRequestBuilder;
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

    private static function getMailerApiClient(MailerClient $mailerClient): ApiClientAbstract
    {
        return match ($mailerClient) {
            MailerClient::SendGrid => self::getSendGridMailerApiClient(),
            MailerClient::Brevo => self::getBrevoMailerApiClient(),
        };
    }

    private static function getMailerRequestBuilder(MailerClient $mailerClient): RequestBuilderAbstract
    {
        return match ($mailerClient) {
            MailerClient::SendGrid => self::getSendGridRequestBuilder(),
            MailerClient::Brevo => self::getBrevoRequestBuilder(),
        };
    }

    public static function getMailerDataLayer(): MailerDataLayer
    {
        return self::getInstance(
            className: 'MailerDataLayer',
            factory: function () {
                $db = self::getDb();

                require_once self::getPathRoot() . '/App/Value/AppMailerTemplate.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Model/MailerItem.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Model/MailerLogItem.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Value/MailerTemplate.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/DataLayer/MailerDataLayer.php';

                return new MailerDataLayer($db);
            },
        );
    }

    public static function getMailerService(): MailerService
    {
        return self::getInstance(
            className: 'MailerService',
            factory: function () {
                require_once self::getPathRoot() . '/App/Value/AppMailerTemplate.php';
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
                return new SendGridRequestBuilder(
                    logger: $logger,
                    fromEmail: $mailerConfig->from->email,
                    fromName: $mailerConfig->from->name,
                );
            },
        );
    }

    public static function getBrevoRequestBuilder(): RequestBuilderAbstract
    {
        return self::getInstance(
            className: 'BrevoRequestBuilder',
            factory: function () {
                $logger = self::getMailerLogger();
                $mailerConfig = self::getConfig()->mailer;

                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/RequestBuilderAbstract.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/Brevo/RequestBuilder.php';
                return new BrevoRequestBuilder(
                    logger: $logger,
                    fromEmail: $mailerConfig->from->email,
                    fromName: $mailerConfig->from->name,
                );
            },
        );
    }

    public static function getMailerApp(bool $isPersistent = true): MailerApp
    {
        return self::getInstance(
            className: 'MailerApp',
            factory: function () use($isPersistent) {
                $mailerConfig = self::getConfig()->mailer;

                require_once self::getPathRoot() . '/Core/Module/Mailer/Entity/Email.php';
                require_once self::getPathRoot() . '/Core/App/LockManager.php';
                require_once self::getPathRoot() . '/Core/App/App.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/MailerApp.php';
                return new MailerApp(
                    logger: self::getMailerLogger(),
                    dataLayer: self::getMailerDataLayer(),
                    apiClient: self::getMailerApiClient($mailerConfig->client),
                    requestBuilder: self::getMailerRequestBuilder($mailerConfig->client),
                    isPersistent: $isPersistent,
                );
            },
            isSingleton: false,
        );
    }

    public static function getSendGridMailerApiClient(): ApiClientAbstract
    {
        return self::getInstance(
            className: 'SendGridApiClient',
            factory: function () {
                $logger = self::getMailerLogger();
                $mailerConfig = self::getConfig()->mailer;

                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/ApiClientAbstract.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/Sendgrid/ApiClient.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/ApiResponse.php';
                return new SendGridApiClient(
                    logger: $logger,
                    baseApiUrl: $mailerConfig->mailerAuthentication->baseApiUrl,
                    apiKey: $mailerConfig->mailerAuthentication->apiKey,
                );
            },
            isSingleton: false,
        );
    }

    public static function getBrevoMailerApiClient(): ApiClientAbstract
    {
        return self::getInstance(
            className: 'BrevoApiClient',
            factory: function () {
                $logger = self::getMailerLogger();
                $mailerConfig = self::getConfig()->mailer;

                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/ApiClientAbstract.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/Brevo/ApiClient.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/ApiResponse.php';
                return new BrevoApiClient(
                    logger: $logger,
                    baseApiUrl: $mailerConfig->mailerAuthentication->baseApiUrl,
                    apiKey: $mailerConfig->mailerAuthentication->apiKey,
                );
            },
            isSingleton: false,
        );
    }
}
