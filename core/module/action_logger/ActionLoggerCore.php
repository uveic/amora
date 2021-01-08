<?php

namespace uve\core\module\action;

use uve\core\Core;
use uve\core\database\MySqlDb;
use uve\core\Logger;
use uve\core\module\action\datalayer\ActionDataLayer;
use uve\core\module\action\service\ActionService;

class ActionLoggerCore extends Core
{

    public static function getDb(): MySqlDb
    {
        return self::getActionDb();
    }

    public static function getActionLogger(): Logger
    {
        return self::getLogger('action_module_logger');
    }

    public static function getActionDataLayer(): ActionDataLayer
    {
        $db = self::getDb();
        $logger = self::getActionLogger();

        return self::getInstance(
            'ActionDataLayer',
            function () use ($db, $logger) {
                require_once self::getPathRoot() . '/core/module/action_logger/model/Action.php';
                require_once self::getPathRoot() . '/core/module/action_logger/datalayer/ActionDataLayer.php';
                return new ActionDataLayer($db, $logger);
            },
            true
        );
    }

    public static function getActionService(): ActionService
    {
        $logger = self::getActionLogger();
        $actionDataLayer = self::getActionDataLayer();

        return self::getInstance(
            'ActionService',
            function () use ($logger, $actionDataLayer) {
                require_once self::getPathRoot() . '/core/module/action_logger/model/Action.php';
                require_once self::getPathRoot() . '/core/module/action_logger/service/ActionService.php';
                return new ActionService($logger, $actionDataLayer);
            },
            true
        );
    }
}
