#!/usr/bin/env php
<?php

namespace Amora\App\Bin;

require_once '../Core.php';

use Throwable;
use Amora\Core\Core;

// change working directory
chdir(dirname(__FILE__));

try {
    Core::initiate(realpath(__DIR__ . '/../..'));
} catch (Throwable $t) {
    echo 'Error initiating application: ' . $t->getMessage() . ' ## Aborting...' . PHP_EOL;
    exit;
}

$CORE_LOOKUP_TABLE_FILE_PATH = realpath(
    Core::getPathRoot() . '/Core/Value/LookupTables/LookupTables.php'
);
$APP_LOOKUP_TABLE_FILE_PATH = realpath(
    Core::getPathRoot() . '/App/Value/LookupTables/LookupTables.php'
);

if (!file_exists($CORE_LOOKUP_TABLE_FILE_PATH)) {
    echo 'Error reading Core lookup tables file: ' . $CORE_LOOKUP_TABLE_FILE_PATH . ' ## Aborting...';
    exit;
}

$coreLookupTables = require($CORE_LOOKUP_TABLE_FILE_PATH);

if (file_exists($APP_LOOKUP_TABLE_FILE_PATH)) {
    $appLookupTables = require($APP_LOOKUP_TABLE_FILE_PATH);
} else {
    $appLookupTables = [];
    echo 'No app lookup tables values found in ' . $APP_LOOKUP_TABLE_FILE_PATH .
        '. Continuing...' . PHP_EOL;
}

$lookupTables = array_merge($appLookupTables, $coreLookupTables);

try {
    $App = Core::getSyncLookupTablesApp();
    $App->run($lookupTables);
} catch (Throwable $t) {
    Core::getDefaultLogger()->logError(
        'Index error' .
        ' - Error: ' . $t->getMessage() .
        ' - Trace: ' . $t->getTraceAsString()
    );

    header('HTTP/1.1 500 Internal Server Error');
    echo 'There was an unexpected error :(';
}
