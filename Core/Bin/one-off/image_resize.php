#!/usr/bin/env php
<?php

namespace Amora\App\Bin;

// change working directory
chdir(__DIR__);

require_once '../../Core.php';

use Amora\Core\Module\Article\ArticleCore;
use Throwable;
use Amora\Core\Core;

try {
    Core::initiate(dirname(__DIR__, 3));
} catch (Throwable $t) {
    echo 'Error initiating application: ' . $t->getMessage() . ' ## Aborting...' . PHP_EOL;
    exit;
}

require_once Core::getPathRoot() . '/App/Value/AppUserRole.php';

$logger = Core::getDefaultLogger();

if (!Core::isRunningInCli()) {
    $logger->logError('Not running in Cli. Aborting...');
    exit;
}

try {
    ArticleCore::getImageResizeApp()->run();
} catch (Throwable $t) {
    $logger->logError(
        'Error running ImageResizeApp: ' . $t->getMessage() . PHP_EOL . $t->getTraceAsString()
    );
}
