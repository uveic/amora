<?php

namespace uve\core\module\mailer;

use uve\core\Core;
use uve\core\database\MySqlDb;
use uve\core\Logger;
use uve\core\mdoule\mailer\app\MailerApp;
use uve\core\module\mailer\app\api\ApiClientAbstract;
use uve\core\module\mailer\app\api\RequestBuilderAbstract;
use uve\core\module\mailer\app\api\sendgrid\ApiClient;
use uve\core\module\mailer\app\api\sendgrid\RequestBuilder;
use uve\core\module\mailer\datalayer\MailerDataLayer;
use uve\core\module\mailer\service\MailerService;

class MailerCore extends Core
{
    public static function getDb(): MySqlDb
    {
        return Core::getMailerDb();
    }

    public static function getMailerLogger(): Logger
    {
        return Core::getLogger('mailer_module_logger');
    }

    public static function getMailerDataLayer(): MailerDataLayer
    {
        return self::getInstance(
            'MailerDataLayer',
            function () {
                $db = self::getDb();
                $logger = self::getMailerLogger();

                require_once self::getPathRoot() . '/core/module/mailer/model/MailerItem.php';
                require_once self::getPathRoot() . '/core/module/mailer/model/MailerLogItem.php';
                require_once self::getPathRoot() . '/core/module/mailer/value/MailerTemplate.php';
                require_once self::getPathRoot() . '/core/module/mailer/datalayer/MailerDataLayer.php';

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

                require_once self::getPathRoot() . '/core/module/mailer/model/MailerItem.php';
                require_once self::getPathRoot() . '/core/module/mailer/model/MailerLogItem.php';
                require_once self::getPathRoot() . '/core/module/mailer/value/MailerTemplate.php';
                require_once self::getPathRoot() . '/core/module/mailer/service/MailerService.php';
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

                $config = Core::getConfig();
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
                $replyToEmail = $config['mailer']['reply_to']['email'] ?? null;
                $replyToName = $config['mailer']['reply_to']['name'] ?? null;

                require_once self::getPathRoot() . '/core/module/mailer/app/api/RequestBuilderAbstract.php';
                require_once self::getPathRoot() . '/core/module/mailer/app/api/sendgrid/RequestBuilder.php';
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

                require_once self::getPathRoot() . '/core/module/mailer/model/Email.php';
                require_once self::getPathRoot() . '/core/app/LockManager.php';
                require_once self::getPathRoot() . '/core/app/App.php';
                require_once self::getPathRoot() . '/core/module/mailer/app/MailerApp.php';
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

                $config = Core::getConfig();
                if (empty($config['mailer']['sendgrid']['base_api_url'])) {
                    $logger->logError("Missing 'mailer.sendgrid.base_api_url' section from config");
                    exit;
                }

                if (empty($config['mailer']['sendgrid']['api_key'])) {
                    $logger->logError("Missing 'mailer.sendgrid.api_key' section from config");
                    exit;
                }

                $baseApiUrl = $config['mailer']['sendgrid']['base_api_url'];
                $apiKey = $config['mailer']['sendgrid']['api_key'];

                require_once self::getPathRoot() . '/core/module/mailer/app/api/ApiClientAbstract.php';
                require_once self::getPathRoot() . '/core/module/mailer/app/api/sendgrid/ApiClient.php';
                require_once self::getPathRoot() . '/core/module/mailer/app/api/ApiResponse.php';
                return new ApiClient($logger, $baseApiUrl, $apiKey);
            },
            false
        );
    }
}
