<?php

namespace Amora\Core\Module\Action\Datalayer;

use Amora\Core\Database\MySqlDb;
use Amora\Core\Logger;
use Amora\Core\Module\Action\Model\Action;

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
