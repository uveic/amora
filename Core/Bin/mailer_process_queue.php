#!/usr/bin/env php
<?php

namespace Amora\App\Bin;

use Throwable;
use Amora\Core\Core;
use Amora\Core\Module\Mailer\MailerCore;

// change working directory
chdir(dirname(__FILE__));

require_once '../Core.php';

try {
    Core::initiate(realpath(__DIR__ . '/../..'));

    $app = MailerCore::getMailerApp();
    $appName = $app->getAppName();
    $app->run();
} catch (Throwable $t) {
    echo 'Error running App '
        . ($appName ?? '')
        . ' ::: '
        . $t->getMessage()
        . PHP_EOL
        . $t->getTraceAsString();
}
