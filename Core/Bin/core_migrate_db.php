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
    Core::getMigrationDbApp(
        Core::getCoreDb(),
        Core::getPathRoot() . '/Core/Database/Migration/files/core'
    )->run($argv);

    Core::getMigrationDbApp(
        Core::getCoreDb(),
        Core::getPathRoot() . '/App/Database/Migration/files/core'
    )->run($argv);

    Core::getMigrationDbApp(
        Core::getAnalyticsDb(),
        Core::getPathRoot() . '/Core/Database/Migration/files/analytics'
    )->run($argv);

    Core::getMigrationDbApp(
        Core::getMailerDb(),
        Core::getPathRoot() . '/Core/Database/Migration/files/mailer'
    )->run($argv);

} catch (Throwable $t) {
    $logger->logError(
        'Error running migration App: ' . $t->getMessage() . PHP_EOL . $t->getTraceAsString()
    );
}
