<?php

namespace Amora\Core;

use Amora\App\AppCore;
use Amora\App\Config\AppConfig;
use Amora\Core\Config\Database;
use Amora\Core\Config\DatabaseBackup;
use Amora\Core\Config\Env;
use Amora\Core\Database\DbBackupApp;
use Amora\Core\Module\Action\ActionLoggerCore;
use Amora\Core\Router\Router;
use Amora\Core\Util\Logger;
use Amora\App\Value\Language;
use Closure;
use Exception;
use Amora\Core\App\SyncLookupTablesApp;
use Amora\Core\Database\Migration\MigrationDbApp;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\LocalisationUtil;

class Core
{
    private static bool $initiated = false;
    private static AppConfig $config;
    private static array $registry = array();
    private static string $pathToRoot;

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

        require_once self::$pathToRoot . '/Core/Entity/Request.php';
        require_once self::$pathToRoot . '/Core/Entity/Response.php';
        require_once self::$pathToRoot . '/Core/Entity/Util/MenuItem.php';
        require_once self::$pathToRoot . '/Core/Entity/Response/HtmlResponseDataAbstract.php';
        require_once self::$pathToRoot . '/Core/Entity/Response/Pagination.php';
        require_once self::$pathToRoot . '/Core/Config/AbstractConfig.php';
        require_once self::$pathToRoot . '/App/Config/AppConfig.php';

        require_once self::$pathToRoot . '/Core/Util/Logger.php';
        require_once self::$pathToRoot . '/Core/Util/NetworkUtil.php';
        require_once self::$pathToRoot . '/Core/Util/DateUtil.php';
        require_once self::$pathToRoot . '/Core/Util/StringUtil.php';
        require_once self::$pathToRoot . '/Core/Util/UrlBuilderUtil.php';

        require_once self::$pathToRoot . '/Core/Module/Article/Value/ArticleType.php';
        require_once self::$pathToRoot . '/Core/Value/Menu/CoreMenu.php';
        require_once self::$pathToRoot . '/Core/Module/DataLayerTrait.php';

        require_once self::$pathToRoot . '/App/Value/Language/Language.php';

        require_once self::$pathToRoot . '/App/AppCore.php';
        require_once self::$pathToRoot . '/Core/Module/User/UserCore.php';
        require_once self::$pathToRoot . '/Core/Module/Article/ArticleCore.php';
        require_once self::$pathToRoot . '/Core/Module/ActionLogger/ActionLoggerCore.php';
        require_once self::$pathToRoot . '/Core/Module/Mailer/MailerCore.php';

        // Application include paths
        set_include_path(get_include_path() . PATH_SEPARATOR . self::$pathToRoot);

        self::$config = AppConfig::get();

        date_default_timezone_set(self::getDefaultTimezone());
        setlocale(LC_ALL, self::$config->phpLocale);

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

    public static function isRunningInCli(): bool
    {
        return php_sapi_name() == 'cli';
    }

    public static function getConfig(): AppConfig
    {
        return self::$config;
    }

    public static function getDefaultTimezone(): string
    {
        return self::$config->timezone;
    }

    public static function getDefaultLanguage(): Language
    {
        return self::$config->defaultSiteLanguage;
    }

    public static function getAllLanguages(): array
    {
        return self::$config->allSiteLanguages;
    }

    public static function isRunningInLiveEnv(): bool
    {
        return self::$config->env === Env::Live;
    }

    public static function updateTimezone(string $newTimezone) {
        if (self::getDefaultTimezone() !== $newTimezone) {
            self::$config->timezone = $newTimezone;
            date_default_timezone_set(self::getDefaultTimezone());
            self::getCoreDb()->updateTimezone();
            self::getActionDb()->updateTimezone();
            self::getMailerDb()->updateTimezone();
        }
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

    public static function getLogger(?string $identifier = null): Logger
    {
        $isRunningInCli = self::isRunningInCli();

        return self::getInstance(
            className: 'AmoraLogger',
            factory: function () use ($isRunningInCli, $identifier) {
                return new Logger(
                    identifier: $identifier,
                    isRunningInCli: $isRunningInCli,
                );
            },
            isSingleton: true,
        );
    }

    public static function getDefaultLogger(): Logger
    {
        return self::getLogger();
    }

    public static function getRouter(): Router
    {
        $actionService = ActionLoggerCore::getActionService();

        return self::getInstance(
            className: 'Router',
            factory: function () use ($actionService) {
                require_once self::$pathToRoot . '/Core/Entity/Response/HtmlResponseData.php';
                require_once self::$pathToRoot . '/App/Router/AppRouterCore.php';
                require_once self::$pathToRoot . '/App/Router/AppRouter.php';
                require_once self::$pathToRoot . '/Core/Router/RouterCore.php';
                require_once self::$pathToRoot . '/Core/Router/Router.php';
                return new Router($actionService);
            },
            isSingleton: true,
        );
    }

    public static function getCoreDb(): MySqlDb
    {
        return self::getDb(self::getConfig()->coreDb);
    }

    public static function getActionDb(): MySqlDb
    {
        return self::getDb(self::getConfig()->actionDb);
    }

    public static function getMailerDb(): MySqlDb
    {
        return self::getDb(self::getConfig()->mailerDb);
    }

    private static function getDb(Database $database): MySqlDb
    {
        return self::getInstance(
            className: $database->name . 'Database',
            factory: function () use ($database) {

                $logger = self::getLogger();

                require_once self::$pathToRoot . '/Core/Value/QueryOrderDirection.php';
                require_once self::$pathToRoot . '/Core/Entity/Util/QueryOrderBy.php';
                require_once self::$pathToRoot . '/Core/Entity/Util/QueryOptions.php';
                require_once self::$pathToRoot . '/Core/Entity/Response/Feedback.php';
                require_once self::$pathToRoot . '/Core/Database/MySqlDb.php';
                return new MySqlDb(
                    logger: $logger,
                    host: $database->host,
                    user: $database->user,
                    password: $database->password,
                    name: $database->name,
                );
            },
            isSingleton: true,
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
            className: 'MigrationDbApp',
            factory: function () use ($db, $pathToMigrationFiles) {
                require_once self::$pathToRoot . '/Core/Database/Migration/MigrationDbApp.php';
                return new MigrationDbApp($db, $pathToMigrationFiles);
            },
            isSingleton: false,
        );
    }

    /**
     * @param MySqlDb $db
     * @param DatabaseBackup $dbBackupConfig
     * @return DbBackupApp
     */
    public static function getDbBackupApp(
        MySqlDb $db,
        DatabaseBackup $dbBackupConfig,
    ): DbBackupApp {
        return self::getInstance(
            className: 'DbBackupApp',
            factory: function () use ($db, $dbBackupConfig) {
                require_once self::$pathToRoot . '/Core/App/LockManager.php';
                require_once self::$pathToRoot . '/Core/App/App.php';
                require_once self::$pathToRoot . '/Core/Database/DbBackupApp.php';
                return new DbBackupApp(
                    logger: self::getDefaultLogger(),
                    db: $db,
                    backupFolderPath: $dbBackupConfig->folderPath,
                    mysqlCommand: $dbBackupConfig->mysqlCommandPath,
                    mysqlDumpCommand: $dbBackupConfig->mysqlDumpCommandPath,
                    gzipCommand: $dbBackupConfig->gzipCommandPath,
                );
            },
            isSingleton: false,
        );
    }

    /**
     * @return SyncLookupTablesApp
     * @throws Exception
     */
    public static function getSyncLookupTablesApp(): SyncLookupTablesApp
    {
        $logger = self::getLogger();

        return self::getInstance(
            className: 'SyncLookupTablesApp',
            factory: function () use ($logger) {
                require_once self::$pathToRoot . '/Core/Entity/Util/LookupTableSettings.php';
                require_once self::$pathToRoot . '/Core/App/SyncLookupTablesApp.php';
                return new SyncLookupTablesApp($logger);
            },
            isSingleton: true,
        );
    }

    /**
     * @param Language $language
     * @param bool $isSingleton
     * @return LocalisationUtil
     */
    public static function getLocalisationUtil(
        Language $language,
        bool $isSingleton = true
    ): LocalisationUtil {
        $logger = self::getLogger();

        return self::getInstance(
            className: 'LocalisationUtil',
            factory: function () use ($logger, $language) {
                require_once self::$pathToRoot . '/Core/Module/User/Service/UserService.php';
                require_once self::$pathToRoot . '/Core/Util/LocalisationUtil.php';
                return new LocalisationUtil($logger, $language);
            },
            isSingleton: $isSingleton,
        );
    }
}
