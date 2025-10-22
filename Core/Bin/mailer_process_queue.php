#!/usr/bin/env php
<?php

namespace Amora\Core\Bin;

use Throwable;
use Amora\Core\Core;
use Amora\Core\Module\Mailer\MailerCore;

// change working directory
chdir(__DIR__);

require_once '../Core.php';

try {
    Core::initiate(dirname(__DIR__, 2));

    $app = MailerCore::getMailerApp();
    $appName = $app->appName;
    $app->run();
} catch (Throwable $t) {
    echo 'Error running App '
        . ($appName ?? '')
        . ' ::: '
        . $t->getMessage()
        . PHP_EOL
        . $t->getTraceAsString();
}
