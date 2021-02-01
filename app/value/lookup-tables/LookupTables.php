<?php

namespace uve\App\Value;

use uve\core\Core;

require_once Core::getPathRoot() . '/app/module/event/value/EventStatus.php';
require_once Core::getPathRoot() . '/app/module/event/value/RsvpType.php';

use uve\app\module\event\value\EventStatus;
use uve\app\module\event\value\RsvpType;

return [
    [
        'table_fields_to_values' => array_values(RsvpType::getAll()),
        'table_name' => 'event_rsvp_type',
        'db' => Core::getCoreDb()
    ],
    [
        'table_fields_to_values' => array_values(EventStatus::getAll()),
        'table_name' => 'event_status',
        'db' => Core::getCoreDb()
    ],
];
