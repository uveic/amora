<?php

namespace Amora\Core\Module\Stats;

use Amora\App\Module\Stats\App\StatsProcessorApp;
use Amora\Core\Core;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Stats\DataLayer\StatsDataLayer;
use Amora\Core\Module\Stats\Service\StatsService;

class StatsCore extends Core
{

    public static function getDb(): MySqlDb
    {
        return self::getStatsDb();
    }

    public static function getStatsLogger(): Logger
    {
        return self::getLogger();
    }

    public static function getStatsDataLayer(): StatsDataLayer
    {
        $db = self::getDb();

        return self::getInstance(
            className: 'StatsDataLayer',
            factory: function () use ($db) {
                require_once self::getPathRoot() . '/Core/Module/Stats/Model/EventRaw.php';
                require_once self::getPathRoot() . '/Core/Module/Stats/Model/EventProcessed.php';
                require_once self::getPathRoot() . '/Core/Module/Stats/DataLayer/StatsDataLayer.php';
                return new StatsDataLayer($db);
            },
        );
    }

    public static function getStatsService(): StatsService
    {
        return self::getInstance(
            className: 'StatsService',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Module/Stats/Model/EventRaw.php';
                require_once self::getPathRoot() . '/Core/Module/Stats/Model/EventProcessed.php';
                require_once self::getPathRoot() . '/Core/Module/Stats/Service/StatsService.php';
                return new StatsService(
                    logger: self::getStatsLogger(),
                    statsDataLayer: self::getStatsDataLayer(),
                );
            },
        );
    }

    public static function getStatsProcessorApp(): StatsProcessorApp
    {
        return self::getInstance(
            className: 'StatsProcessorApp',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Router/Router.php';
                require_once self::getPathRoot() . '/App/Router/AppRouter.php';
                require_once self::getPathRoot() . '/Core/Entity/Util/UserAgentInfo.php';
                require_once self::getPathRoot() . '/Core/Util/UserAgentParserUtil.php';
                require_once self::getPathRoot() . '/Core/Module/Stats/Value/BotUrl.php';
                require_once self::getPathRoot() . '/Core/Module/Stats/Value/BotUserAgent.php';
                require_once self::getPathRoot() . '/Core/Module/Stats/Value/EventType.php';
                require_once self::getPathRoot() . '/Core/Module/Stats/Model/EventRaw.php';
                require_once self::getPathRoot() . '/Core/App/LockManager.php';
                require_once self::getPathRoot() . '/Core/App/App.php';
                require_once self::getPathRoot() . '/Core/Module/Stats/App/StatsProcessorApp.php';

                $siteUrl = parse_url(self::getConfig()->baseUrl, PHP_URL_HOST);
                $siteUrl = 'feirafranca.pontevedra.gal'; // ToDo: remove this line
                return new StatsProcessorApp(
                    logger: self::getStatsLogger(),
                    statsService: self::getStatsService(),
                    siteUrl: $siteUrl,
                );
            },
        );
    }
}
