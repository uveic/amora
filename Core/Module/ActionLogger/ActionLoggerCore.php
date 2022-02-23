<?php

namespace Amora\Core\Module\Action;

use Amora\Core\Core;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Logger;
use Amora\Core\Module\Action\Datalayer\ActionDataLayer;
use Amora\Core\Module\Action\Service\ActionService;

class ActionLoggerCore extends Core
{

    public static function getDb(): MySqlDb
    {
        return self::getActionDb();
    }

    public static function getActionLogger(): Logger
    {
        return self::getLogger();
    }

    public static function getActionDataLayer(): ActionDataLayer
    {
        $db = self::getDb();

        return self::getInstance(
            className: 'ActionDataLayer',
            factory: function () use ($db) {
                require_once self::getPathRoot() . '/Core/Module/ActionLogger/Model/Action.php';
                require_once self::getPathRoot() . '/Core/Module/ActionLogger/Datalayer/ActionDataLayer.php';
                return new ActionDataLayer($db);
            },
            isSingleton: true,
        );
    }

    public static function getActionService(): ActionService
    {
        $logger = self::getActionLogger();
        $actionDataLayer = self::getActionDataLayer();

        return self::getInstance(
            className: 'ActionService',
            factory: function () use ($logger, $actionDataLayer) {
                require_once self::getPathRoot() . '/Core/Module/ActionLogger/Model/Action.php';
                require_once self::getPathRoot() . '/Core/Module/ActionLogger/Service/ActionService.php';
                return new ActionService($logger, $actionDataLayer);
            },
            isSingleton: true,
        );
    }
}
