<?php

namespace Amora\App;

use Amora\Core\Core;

class AppCore extends Core
{
    public static function initiateApp(): void
    {
        require_once self::getPathRoot() . '/App/Value/AppMenu.php';
    }
}
