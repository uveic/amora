#!/usr/bin/env php
<?php

namespace Amora\Core\Bin;

use Amora\Core\Module\Analytics\AnalyticsCore;
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

require_once Core::getPathRoot() . '/vendor/autoload.php';

$logger = Core::getDefaultLogger();

if (!Core::isRunningInCli()) {
    $logger->logError('Not running in Cli. Aborting...');
    exit;
}

try {
    AnalyticsCore::getAnalyticsProcessorApp()->run();
} catch (Throwable $t) {
    $logger->logError(
        'Error running analytics processor App: ' . $t->getMessage() . PHP_EOL . $t->getTraceAsString()
    );
}
