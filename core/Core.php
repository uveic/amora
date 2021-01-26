<?php

namespace uve\core;

use Closure;
use Exception;
use uve\core\cli\SyncLookupTablesApp;
use uve\core\database\migration\MigrationDbApp;
use uve\core\database\MySqlDb;
use uve\core\util\LocalisationUtil;
use uve\router\Router;

class Core
{
    private static bool $initiated = false;
    private static Config $config;
    private static array $registry = array();

    private static string $pathToRoot;
    private static string $timezone;
    private static string $phpLocale;

    /**
     * @param string $pathToRoot
     * @throws Exception
     */
    public static function initiate(string $pathToRoot): void
    {
        if (self::$initiated) {
            return;
        }

        self::$pathToRoot = $pathToRoot;

        require_once self::getPathRoot() . '/core/model/File.php';
        require_once self::getPathRoot() . '/core/model/Request.php';
        require_once self::getPathRoot() . '/core/model/Response.php';
        require_once self::getPathRoot() . '/core/model/response/HtmlResponseDataAbstract.php';
        require_once self::getPathRoot() . '/core/model/response/HtmlResponseData.php';
        require_once self::getPathRoot() . '/core/model/response/HtmlResponseDataAuthorised.php';
        require_once self::getPathRoot() . '/core/Logger.php';
        require_once self::getPathRoot() . '/core/Config.php';

        require_once self::getPathRoot() . '/core/util/NetworkUtil.php';
        require_once self::getPathRoot() . '/core/util/DateUtil.php';
        require_once self::getPathRoot() . '/core/util/StringUtil.php';

        require_once self::getPathRoot() . '/core/value/language/Language.php';

        require_once self::getPathRoot() . '/core/module/user/UserCore.php';
        require_once self::getPathRoot() . '/core/module/article/ArticleCore.php';
        require_once self::getPathRoot() . '/core/module/action_logger/ActionLoggerCore.php';
        require_once self::getPathRoot() . '/core/module/mailer/MailerCore.php';

        // Application include paths
        set_include_path(get_include_path() . PATH_SEPARATOR . self::getPathRoot());

        self::$config = new Config();
        self::$timezone = self::$config->get('timezone') ?? 'UTC';
        self::$phpLocale = self::$config->get('php_locale') ?? 'en';

        date_default_timezone_set(self::getDefaultTimezone());
        setlocale(LC_ALL, self::getPhpLocale());

        if (!self::isRunningInLiveEnv()) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }

        self::$initiated = true; // Done!
    }

    public static function getPathRoot(): string
    {
        return self::$pathToRoot;
    }

    public static function getPhpLocale(): string
    {
        return self::$phpLocale;
    }

    public static function isRunningInCli(): bool
    {
        return php_sapi_name() == 'cli';
    }

    public static function getConfig(): array
    {
        return self::$config->getConfig();
    }

    public static function getDefaultTimezone(): string
    {
        return self::$timezone;
    }

    public static function getConfigValue(string $key)
    {
        return self::$config->get($key);
    }

    public static function isRunningInLiveEnv(): bool
    {
        return self::$config->get('env') === 'live';
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////
    // DI manager

    /**
     * @param string $className
     * @param Closure $factory
     * @param bool $isSingleton
     * @return mixed
     */
    protected static function getInstance(
        string $className,
        Closure $factory,
        bool $isSingleton = true
    ) {
        if (!self::$initiated) {
            echo 'You forgot to call Core::initiate()!' . PHP_EOL;
            exit;
        }

        if ($isSingleton) {
            if (!isset(self::$registry[$className])) {
                self::$registry[$className] = $factory();
            }

            return self::$registry[$className];
        }

        return $factory();
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////
    // Class factories

    public static function getLogger(string $appName): Logger
    {
        if (empty($appName)) {
            $appName = 'uve_core';
        }

        $isRunningInCli = self::isRunningInCli();

        return self::getInstance(
            'Logger',
            function () use ($appName, $isRunningInCli) {
                return new Logger($appName, $isRunningInCli);
            },
            true
        );
    }

    public static function getDefaultLogger(): Logger
    {
        return self::getLogger('default_logger');
    }

    public static function getRouter(): Router
    {
        return self::getInstance(
            'Router',
            function () {
                require_once self::getPathRoot() . '/router/RouterCore.php';
                require_once self::getPathRoot() . '/router/Router.php';
                return new Router();
            },
            true
        );
    }

    public static function getCoreDb(): MySqlDb
    {
        return self::getDb('core');
    }

    public static function getActionDb(): MySqlDb
    {
        return self::getDb('action');
    }

    public static function getMailerDb(): MySqlDb
    {
        return self::getDb('mailer');
    }

    private static function getDb(string $dbIdentifier): MySqlDb
    {
        $dbClassname = ucfirst($dbIdentifier) . 'Db';

        return self::getInstance(
            $dbClassname,
            function () use ($dbIdentifier) {
                $config = Core::getConfig();
                if (empty($config['db'])) {
                    self::getDefaultLogger()->logError("Missing 'db' section from config");
                    exit;
                }

                if (empty($config['db'][$dbIdentifier])) {
                    self::getDefaultLogger()->logError("Missing database name section from config");
                    exit;
                }

                if (empty($config['db'][$dbIdentifier]['host'])) {
                    self::getDefaultLogger()->logError("Missing 'db.host' from config");
                    exit;
                }

                if (empty($config['db'][$dbIdentifier]['user'])) {
                    self::getDefaultLogger()->logError("Missing 'db.user' from config");
                    exit;
                }

                if (!isset($config['db'][$dbIdentifier]['password'])) {
                    self::getDefaultLogger()->logError("Missing 'db.password' from config");
                    exit;
                }

                if (empty($config['db'][$dbIdentifier]['name'])) {
                    self::getDefaultLogger()->logError("Missing 'db.name' from config");
                    exit;
                }

                $logger = Core::getLogger($dbIdentifier . '_database');
                $host = $config['db'][$dbIdentifier]['host'];
                $user = $config['db'][$dbIdentifier]['user'];
                $pass = $config['db'][$dbIdentifier]['password'];
                $dbName = $config['db'][$dbIdentifier]['name'];

                require_once self::getPathRoot() . '/core/database/MySqlDb.php';
                return new MySqlDb($logger, $host, $user, $pass, $dbName);
            },
            true
        );
    }

    /**
     * @param MySqlDb $db
     * @param string $pathToMigrationFiles
     * @return MigrationDbApp
     * @throws Exception
     */
    public static function getMigrationDbApp(
        MySqlDb $db,
        string $pathToMigrationFiles
    ): MigrationDbApp {
        return self::getInstance(
            'MigrationDbApp',
            function () use ($db, $pathToMigrationFiles) {
                require_once self::getPathRoot() . '/core/database/migration/MigrationDbApp.php';
                return new MigrationDbApp($db, $pathToMigrationFiles);
            },
            false
        );
    }

    /**
     * @return SyncLookupTablesApp
     * @throws Exception
     */
    public static function getSyncLookupTablesApp(): SyncLookupTablesApp
    {
        $logger = Core::getLogger('sync_lookup_tables');

        return self::getInstance(
            'SyncLookupTablesApp',
            function () use ($logger) {
                require_once self::getPathRoot() . '/core/lookup_tables_sync/SyncLookupTablesApp.php';
                return new SyncLookupTablesApp($logger);
            },
            true
        );
    }

    /**
     * @param string $siteLanguage
     * @return LocalisationUtil
     */
    public static function getLocalisationUtil(string $siteLanguage): LocalisationUtil
    {
        $logger = Core::getLogger('localisation_util');
        $defaultSiteLanguage = strtoupper(self::getConfigValue('default_site_language') ?? 'EN');

        return self::getInstance(
            'LocalisationUtil',
            function () use ($logger, $defaultSiteLanguage, $siteLanguage) {
                require_once self::getPathRoot() . '/core/module/user/service/UserService.php';
                require_once self::getPathRoot() . '/core/util/LocalisationUtil.php';
                return new LocalisationUtil($logger, $defaultSiteLanguage, $siteLanguage);
            },
            true
        );
    }
}
