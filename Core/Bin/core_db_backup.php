#!/usr/bin/env php
<?php

namespace Amora\Core\Bin;

use Throwable;
use Amora\Core\Core;

// change working directory
chdir(__DIR__);

require_once '../Core.php';

try {
    Core::initiate(dirname(__DIR__, 2));
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
    Core::getDbBackupApp(Core::getCoreDb(), Core::getConfig()->databaseBackup)->run();
    Core::getDbBackupApp(Core::getAnalyticsDb(), Core::getConfig()->databaseBackup)->run();
    Core::getDbBackupApp(Core::getMailerDb(), Core::getConfig()->databaseBackup)->run();
} catch (Throwable $t) {
    $logger->logError(
        'Error running database backup App: ' . $t->getMessage() . PHP_EOL . $t->getTraceAsString()
    );
}
