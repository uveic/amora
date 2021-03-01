<?php

namespace Amora\Core\Module\mailer;

use Amora\Core\Core;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Logger;
use Amora\Core\Module\Mailer\App\MailerApp;
use Amora\Core\Module\Mailer\App\Api\ApiClientAbstract;
use Amora\Core\Module\Mailer\App\Api\RequestBuilderAbstract;
use Amora\Core\Module\Mailer\App\Api\Sendgrid\ApiClient;
use Amora\Core\Module\Mailer\App\Api\Sendgrid\RequestBuilder;
use Amora\Core\Module\Mailer\Datalayer\MailerDataLayer;
use Amora\Core\Module\Mailer\Service\MailerService;

class MailerCore extends Core
{
    public static function getDb(): MySqlDb
    {
        return self::getMailerDb();
    }

    public static function getMailerLogger(): Logger
    {
        return self::getLogger('MailerModuleLogger');
    }

    public static function getMailerDataLayer(): MailerDataLayer
    {
        return self::getInstance(
            'MailerDataLayer',
            function () {
                $db = self::getDb();
                $logger = self::getMailerLogger();

                require_once self::getPathRoot() . '/Core/Module/Mailer/Model/MailerItem.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Model/MailerLogItem.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Value/MailerTemplate.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Datalayer/MailerDataLayer.php';

                return new MailerDataLayer($db, $logger);
            },
            true
        );
    }

    public static function getMailerService(): MailerService
    {
        return self::getInstance(
            'MailerService',
            function () {
                $logger = self::getMailerLogger();
                $mailerDataLayer = self::getMailerDataLayer();

                require_once self::getPathRoot() . '/Core/Module/Mailer/Model/MailerItem.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Model/MailerLogItem.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Value/MailerTemplate.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/Service/MailerService.php';
                return new MailerService($logger, $mailerDataLayer);
            },
            true
        );
    }

    public static function getSendGridRequestBuilder(): RequestBuilderAbstract
    {
        return self::getInstance(
            'SendGridRequestBuilder',
            function () {
                $logger = self::getMailerLogger();

                $config = self::getConfig();
                if (empty($config['mailer']['from']['email'])) {
                    $logger->logError("Missing 'mailer.from.email' section from config");
                    exit;
                }

                if (empty($config['mailer']['from']['name'])) {
                    $logger->logError("Missing 'mailer.from.name' section from config");
                    exit;
                }

                $fromMail = $config['mailer']['from']['email'];
                $fromName = $config['mailer']['from']['name'];
                $replyToEmail = $config['mailer']['replyTo']['email'] ?? null;
                $replyToName = $config['mailer']['replyTo']['name'] ?? null;

                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/RequestBuilderAbstract.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/Sendgrid/RequestBuilder.php';
                return new RequestBuilder(
                    $logger,
                    $fromMail,
                    $fromName,
                    $replyToEmail,
                    $replyToName
                );
            },
            true
        );
    }

    public static function getMailerApp(): MailerApp {
        return self::getInstance(
            'MailerApp',
            function () {
                $logger = self::getMailerLogger();
                $dataLayer = self::getMailerDataLayer();
                $apiClient = self::getSendGridMailerApiClient();
                $requestBuilder = self::getSendGridRequestBuilder();

                require_once self::getPathRoot() . '/Core/Module/Mailer/Model/Email.php';
                require_once self::getPathRoot() . '/Core/App/LockManager.php';
                require_once self::getPathRoot() . '/Core/App/App.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/MailerApp.php';
                return new MailerApp($logger, $dataLayer, $apiClient, $requestBuilder);
            },
            false
        );
    }

    public static function getSendGridMailerApiClient(): ApiClientAbstract
    {
        return self::getInstance(
            'ApiClient',
            function () {
                $logger = self::getMailerLogger();

                $config = self::getConfig();
                if (empty($config['mailer']['sendgrid']['baseApiUrl'])) {
                    $logger->logError("Missing 'mailer.sendgrid.baseApiUrl' section from config");
                    exit;
                }

                if (empty($config['mailer']['sendgrid']['apiKey'])) {
                    $logger->logError("Missing 'mailer.sendgrid.apiKey' section from config");
                    exit;
                }

                $baseApiUrl = $config['mailer']['sendgrid']['baseApiUrl'];
                $apiKey = $config['mailer']['sendgrid']['apiKey'];

                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/ApiClientAbstract.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/Sendgrid/ApiClient.php';
                require_once self::getPathRoot() . '/Core/Module/Mailer/App/Api/ApiResponse.php';
                return new ApiClient($logger, $baseApiUrl, $apiKey);
            },
            false
        );
    }
}
