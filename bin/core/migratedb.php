#!/usr/bin/env php
<?php

namespace uve\app\bin\core;

use Throwable;
use uve\core\Core;

// change working directory
chdir(dirname(__FILE__));

require_once '../../core/Core.php';

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
        Core::getPathRoot() . '/core/database/migration/migrate_files/core'
    )->run($argv);

    Core::getMigrationDbApp(
        Core::getActionDb(),
        Core::getPathRoot() . '/core/database/migration/migrate_files/action'
    )->run($argv);

    Core::getMigrationDbApp(
        Core::getMailerDb(),
        Core::getPathRoot() . '/core/database/migration/migrate_files/mailer'
    )->run($argv);

} catch (Throwable $t) {
    $logger->logError(
        'Error running migration: ' . $t->getMessage() . PHP_EOL . $t->getTraceAsString()
    );
}
