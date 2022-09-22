<?php

namespace Amora\Core\Module\Action\Datalayer;

use Amora\Core\Database\MySqlDb;
use Amora\Core\Module\Action\Model\Action;

class ActionDataLayer
{
    const ACTION_TABLE_NAME = 'action';

    public function __construct(
        private MySqlDb $db,
    ) {}

    public function storeAction(Action $action): Action
    {
        $res = $this->db->insert(self::ACTION_TABLE_NAME, $action->asArray());

        $action->id = $res;
        return $action;
    }
}
