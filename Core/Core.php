<?php

namespace Amora\Core;

use Amora\App\AppCore;
use Closure;
use Exception;
use Amora\Core\App\SyncLookupTablesApp;
use Amora\Core\Database\Migration\MigrationDbApp;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\LocalisationUtil;
use Amora\Router\Router;

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

        require_once self::getPathRoot() . '/Core/Model/File.php';
        require_once self::getPathRoot() . '/Core/Model/Request.php';
        require_once self::getPathRoot() . '/Core/Model/Response.php';
        require_once self::getPathRoot() . '/Core/Model/Menu/MenuItem.php';
        require_once self::getPathRoot() . '/Core/Model/Response/HtmlResponseDataAbstract.php';
        require_once self::getPathRoot() . '/Core/Model/Util/LookupTableBasicValue.php';
        require_once self::getPathRoot() . '/Core/Logger.php';
        require_once self::getPathRoot() . '/Core/Config.php';

        require_once self::getPathRoot() . '/Core/Util/NetworkUtil.php';
        require_once self::getPathRoot() . '/Core/Util/DateUtil.php';
        require_once self::getPathRoot() . '/Core/Util/StringUtil.php';
        require_once self::getPathRoot() . '/Core/Util/UrlBuilderUtil.php';

        require_once self::getPathRoot() . '/Core/Value/Language/Language.php';
        require_once self::getPathRoot() . '/Core/Value/Menu/CoreMenu.php';

        require_once self::getPathRoot() . '/App/AppCore.php';
        require_once self::getPathRoot() . '/Core/Module/User/UserCore.php';
        require_once self::getPathRoot() . '/Core/Module/Article/ArticleCore.php';
        require_once self::getPathRoot() . '/Core/Module/ActionLogger/ActionLoggerCore.php';
        require_once self::getPathRoot() . '/Core/Module/Mailer/MailerCore.php';

        // Application include paths
        set_include_path(get_include_path() . PATH_SEPARATOR . self::getPathRoot());

        self::$config = new Config();
        self::$timezone = self::$config->get('timezone') ?? 'UTC';
        self::$phpLocale = self::$config->get('phpLocale') ?? 'en';

        date_default_timezone_set(self::getDefaultTimezone());
        setlocale(LC_ALL, self::getPhpLocale());

        if (!self::isRunningInLiveEnv()) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }

        AppCore::initiateApp();

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
            $appName = 'AmoraCore';
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
        return self::getLogger('CoreDefaultLogger');
    }

    public static function getRouter(): Router
    {
        return self::getInstance(
            'Router',
            function () {
                require_once self::getPathRoot() . '/Core/Model/Response/HtmlResponseData.php';
                require_once self::getPathRoot() . '/Router/RouterCore.php';
                require_once self::getPathRoot() . '/Router/Router.php';
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
        $dbClassname = ucfirst($dbIdentifier) . 'Database';

        return self::getInstance(
            $dbClassname,
            function () use ($dbIdentifier) {
                $config = self::getConfig();
                if (empty($config['database'])) {
                    self::getDefaultLogger()->logError("Missing 'database' section from config");
                    exit;
                }

                if (empty($config['database'][$dbIdentifier])) {
                    self::getDefaultLogger()->logError("Missing database section from config");
                    exit;
                }

                if (empty($config['database'][$dbIdentifier]['host'])) {
                    self::getDefaultLogger()->logError("Missing 'database.host' from config");
                    exit;
                }

                if (empty($config['database'][$dbIdentifier]['user'])) {
                    self::getDefaultLogger()->logError("Missing 'database.user' from config");
                    exit;
                }

                if (!isset($config['database'][$dbIdentifier]['password'])) {
                    self::getDefaultLogger()->logError("Missing 'database.password' from config");
                    exit;
                }

                if (empty($config['database'][$dbIdentifier]['name'])) {
                    self::getDefaultLogger()->logError("Missing 'database.name' from config");
                    exit;
                }

                $logger = self::getLogger($dbIdentifier . 'Database');
                $host = $config['database'][$dbIdentifier]['host'];
                $user = $config['database'][$dbIdentifier]['user'];
                $pass = $config['database'][$dbIdentifier]['password'];
                $dbName = $config['database'][$dbIdentifier]['name'];

                require_once self::getPathRoot() . '/Core/Model/Util/QueryOptions.php';
                require_once self::getPathRoot() . '/Core/Database/Model/TransactionResponse.php';
                require_once self::getPathRoot() . '/Core/Database/MySqlDb.php';
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
                require_once self::getPathRoot() . '/Core/Database/Migration/MigrationDbApp.php';
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
        $logger = self::getLogger('SyncLookupTables');

        return self::getInstance(
            'SyncLookupTablesApp',
            function () use ($logger) {
                require_once self::getPathRoot() . '/Core/Model/Util/LookupTableSettings.php';
                require_once self::getPathRoot() . '/Core/App/SyncLookupTablesApp.php';
                return new SyncLookupTablesApp($logger);
            },
            true
        );
    }

    /**
     * @param string $languageIsoCode
     * @param bool $isSingleton
     * @return LocalisationUtil
     */
    public static function getLocalisationUtil(
        string $languageIsoCode,
        bool $isSingleton = true
    ): LocalisationUtil {
        $logger = self::getLogger('LocalisationUtil');
        $defaultSiteLanguage = strtoupper(self::getConfigValue('defaultSiteLanguage') ?? 'EN');

        return self::getInstance(
            'LocalisationUtil',
            function () use ($logger, $defaultSiteLanguage, $languageIsoCode) {
                require_once self::getPathRoot() . '/Core/Module/User/Service/UserService.php';
                require_once self::getPathRoot() . '/Core/Util/LocalisationUtil.php';
                return new LocalisationUtil($logger, $defaultSiteLanguage, $languageIsoCode);
            },
            $isSingleton
        );
    }
}
