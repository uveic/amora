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
use Amora\Core\Module\Mailer\App\Api\Lettermint\ApiClient as LettermintGridApiClient;
use Amora\Core\Module\Mailer\App\Api\Lettermint\RequestBuilder as LettermintRequestBuilder;
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
            MailerClient::Lettermint => self::getLettermintMailerApiClient(),
        };
    }

    private static function getMailerRequestBuilder(MailerClient $mailerClient): RequestBuilderAbstract
    {
        return match ($mailerClient) {
            MailerClient::SendGrid => self::getSendGridRequestBuilder(),
            MailerClient::Brevo => self::getBrevoRequestBuilder(),
            MailerClient::Lettermint => self::getLettermintRequestBuilder(),
        };
    }

    public static function getMailerDataLayer(): MailerDataLayer
    {
        return self::getInstance(
            className: 'MailerDataLayer',
            factory: static function () {
                require_once self::getPathRoot() . '/App/Value/AppMailerTemplate.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Model/MailerItem.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Model/MailerLogItem.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Value/MailerTemplate.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/DataLayer/MailerDataLayer.php';

                return new MailerDataLayer(
                    db: self::getDb(),
                    logger: self::getLogger(),
                );
            },
        );
    }

    public static function getMailerService(): MailerService
    {
        return self::getInstance(
            className: 'MailerService',
            factory: static function () {
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
            factory: static function () {
                $mailerConfig = self::getConfig()->mailer;

                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/RequestBuilderAbstract.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/Sendgrid/RequestBuilder.php';
                return new SendGridRequestBuilder(
                    logger: self::getMailerLogger(),
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
            factory: static function () {
                $mailerConfig = self::getConfig()->mailer;

                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/RequestBuilderAbstract.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/Brevo/RequestBuilder.php';
                return new BrevoRequestBuilder(
                    logger: self::getMailerLogger(),
                    fromEmail: $mailerConfig->from->email,
                    fromName: $mailerConfig->from->name,
                );
            },
        );
    }

    public static function getLettermintRequestBuilder(): RequestBuilderAbstract
    {
        return self::getInstance(
            className: 'LettermintRequestBuilder',
            factory: static function () {
                $mailerConfig = self::getConfig()->mailer;

                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/RequestBuilderAbstract.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/Lettermint/RequestBuilder.php';
                return new LettermintRequestBuilder(
                    logger: self::getMailerLogger(),
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
            factory: static function () use($isPersistent) {
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
            factory: static function () {
                $mailerConfig = self::getConfig()->mailer;

                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/ApiClientAbstract.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/Sendgrid/ApiClient.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/ApiResponse.php';
                return new SendGridApiClient(
                    logger: self::getMailerLogger(),
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
            factory: static function () {
                $mailerConfig = self::getConfig()->mailer;

                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/ApiClientAbstract.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/Brevo/ApiClient.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/ApiResponse.php';
                return new BrevoApiClient(
                    logger: self::getMailerLogger(),
                    baseApiUrl: $mailerConfig->mailerAuthentication->baseApiUrl,
                    apiKey: $mailerConfig->mailerAuthentication->apiKey,
                );
            },
            isSingleton: false,
        );
    }

    public static function getLettermintMailerApiClient(): ApiClientAbstract
    {
        return self::getInstance(
            className: 'LettermintApiClient',
            factory: static function () {
                $mailerConfig = self::getConfig()->mailer;

                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/ApiClientAbstract.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/Lettermint/ApiClient.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/ApiResponse.php';
                return new LettermintGridApiClient(
                    logger: self::getMailerLogger(),
                    baseApiUrl: $mailerConfig->mailerAuthentication->baseApiUrl,
                    apiKey: $mailerConfig->mailerAuthentication->apiKey,
                );
            },
            isSingleton: false,
        );
    }
}
