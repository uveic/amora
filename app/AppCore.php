<?php

namespace App;

use uve\core\Core;

class AppCore extends Core
{
    public static function initiateApp(): void
    {
        require_once self::getPathRoot() . '/app/value/menu/AppMenu.php';
    }
}
