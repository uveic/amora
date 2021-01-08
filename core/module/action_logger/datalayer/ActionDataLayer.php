<?php

namespace uve\core\module\action\datalayer;

use uve\core\database\MySqlDb;
use uve\core\Logger;
use uve\core\module\action\model\Action;

class ActionDataLayer
{
    const ACTION_TABLE_NAME = 'action';

    private MySqlDb $db;
    private Logger $logger;

    public function __construct(MySqlDb $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function storeAction(Action $action): Action
    {
        $res = $this->db->insert(self::ACTION_TABLE_NAME, $action->asArray());

        $action->setId($res);
        return $action;
    }
}
