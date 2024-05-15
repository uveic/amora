#!/usr/bin/env php
<?php

namespace Amora\App\Bin;

// change working directory
chdir(dirname(__FILE__));

require_once '../../Core.php';

use Amora\Core\Module\Article\ArticleCore;
use Throwable;
use Amora\Core\Core;

try {
    Core::initiate(realpath(__DIR__ . '/../../..'));
} catch (Throwable $t) {
    echo 'Error initiating application: ' . $t->getMessage() . ' ## Aborting...' . PHP_EOL;
    exit;
}

$logger = Core::getDefaultLogger();

if (!Core::isRunningInCli()) {
    $logger->logError('Not running in Cli. Aborting...');
    exit;
}

try {
    ArticleCore::getMediaRemoveApp()->run();
} catch (Throwable $t) {
    $logger->logError(
        'Error running MediaRemoveApp: ' . $t->getMessage() . PHP_EOL . $t->getTraceAsString()
    );
}
