<?php

namespace Amora\Core\Module\Analytics;

use Amora\App\Module\Analytics\App\AnalyticsProcessorApp;
use Amora\Core\Core;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Analytics\DataLayer\AnalyticsDataLayer;
use Amora\Core\Module\Analytics\Service\AnalyticsService;

class AnalyticsCore extends Core
{

    public static function getDb(): MySqlDb
    {
        return self::getAnalyticsDb();
    }

    public static function getAnalyticsLogger(): Logger
    {
        return self::getLogger();
    }

    public static function getAnalyticsDataLayer(): AnalyticsDataLayer
    {
        $db = self::getDb();

        return self::getInstance(
            className: 'AnalyticsDataLayer',
            factory: function () use ($db) {
                require_once self::getPathRoot() . '/Core/Module/Analytics/Model/EventRaw.php';
                require_once self::getPathRoot() . '/Core/Module/Analytics/Model/EventProcessed.php';
                require_once self::getPathRoot() . '/Core/Module/Analytics/DataLayer/AnalyticsDataLayer.php';
                return new AnalyticsDataLayer($db);
            },
        );
    }

    public static function getAnalyticsService(): AnalyticsService
    {
        return self::getInstance(
            className: 'AnalyticsService',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Value/AggregateBy.php';
                require_once self::getPathRoot() . '/Core/Value/Country/Country.php';
                require_once self::getPathRoot() . '/Core/Module/Analytics/Entity/ReportViewCount.php';
                require_once self::getPathRoot() . '/Core/Module/Analytics/Entity/PageViewCount.php';
                require_once self::getPathRoot() . '/Core/Module/Analytics/Entity/PageView.php';
                require_once self::getPathRoot() . '/Core/Module/Analytics/Value/CountDbColumn.php';
                require_once self::getPathRoot() . '/Core/Module/Analytics/Value/Period.php';
                require_once self::getPathRoot() . '/Core/Module/Analytics/Value/EventType.php';
                require_once self::getPathRoot() . '/Core/Module/Analytics/Model/EventRaw.php';
                require_once self::getPathRoot() . '/Core/Module/Analytics/Model/EventProcessed.php';
                require_once self::getPathRoot() . '/Core/Module/Analytics/Service/AnalyticsService.php';
                return new AnalyticsService(
                    logger: self::getAnalyticsLogger(),
                    analyticsDataLayer: self::getAnalyticsDataLayer(),
                );
            },
        );
    }

    public static function getAnalyticsProcessorApp(): AnalyticsProcessorApp
    {
        return self::getInstance(
            className: 'AnalyticsProcessorApp',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Router/Router.php';
                require_once self::getPathRoot() . '/App/Router/AppRouter.php';
                require_once self::getPathRoot() . '/Core/Entity/Util/UserAgentInfo.php';
                require_once self::getPathRoot() . '/Core/Util/UserAgentParserUtil.php';
                require_once self::getPathRoot() . '/Core/Module/Analytics/Value/EventType.php';
                require_once self::getPathRoot() . '/Core/Module/Analytics/Model/EventRaw.php';
                require_once self::getPathRoot() . '/Core/App/LockManager.php';
                require_once self::getPathRoot() . '/Core/App/App.php';
                require_once self::getPathRoot() . '/Core/Module/Analytics/App/AnalyticsProcessorApp.php';

                $siteUrl = parse_url(self::getConfig()->baseUrl, PHP_URL_HOST);
                return new AnalyticsProcessorApp(
                    logger: self::getAnalyticsLogger(),
                    analyticsService: self::getAnalyticsService(),
                    siteUrl: $siteUrl,
                );
            },
        );
    }
}
