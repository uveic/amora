<?php

namespace Amora\Core\Module\Stats;

use Amora\Core\Core;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Stats\Datalayer\StatsDataLayer;
use Amora\Core\Module\Stats\Service\StatsService;

class StatsCore extends Core
{

    public static function getDb(): MySqlDb
    {
        return self::getStatsDb();
    }

    public static function getStats(): Logger
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
                require_once self::getPathRoot() . '/Core/Module/Stats/Service/StatsService.php';
                return new StatsService(
                    logger: self::getStats(),
                    statsDataLayer: self::getStatsDataLayer(),
                );
            },
        );
    }
}
