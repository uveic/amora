#!/usr/bin/env php
<?php

namespace uve\app\bin\mailer;

use Throwable;
use uve\core\Core;
use uve\core\module\mailer\MailerCore;

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
