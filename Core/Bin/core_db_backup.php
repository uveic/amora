#!/usr/bin/env php
<?php

namespace Amora\Core\Bin;

use Throwable;
use Amora\Core\Core;

// change working directory
chdir(dirname(__FILE__));

require_once '../Core.php';

try {
    Core::initiate(realpath(__DIR__ . '/../..'));
} catch (Throwable $t) {
    echo 'Error initiating application: ' . $t->getMessage() . ' ## Aborting...';
    exit;
}

$logger = Core::getDefaultLogger();

if (!Core::isRunningInCli()) {
    $logger->logError('Not running in Cli. Aborting...');
    exit;
}

try {
    Core::getDbBackupApp(Core::getCoreDb())->run();
    Core::getDbBackupApp(Core::getActionDb())->run();
    Core::getDbBackupApp(Core::getMailerDb())->run();
} catch (Throwable $t) {
    $logger->logError(
        'Error running migration: ' . $t->getMessage() . PHP_EOL . $t->getTraceAsString()
    );
}
