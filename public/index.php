<?php

use uve\core\Core;
use uve\core\model\Request;
use uve\core\util\NetworkUtil;

require_once '../core/Core.php';
try {
    Core::initiate(realpath(__DIR__ . '/..'));
} catch (Throwable $t) {
    // Return a 500 Internal Error as fallback
    Core::getDefaultLogger()->logError(
        'Index error' .
        ' - Error: ' . $t->getMessage() .
        ' - Trace: ' . $t->getTraceAsString()
    );

    header('HTTP/1.1 500 Internal Server Error');
    echo 'There was an unexpected error :(';
}

require_once Core::getPathRoot() . '/vendor/autoload.php';

$body = file_get_contents('php://input');
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), " /");

$request = new Request(
    NetworkUtil::determineClientIp(),
    $_SERVER['HTTP_USER_AGENT'] ?? null,
    $_SERVER['REQUEST_METHOD'],
    $path,
    $_SERVER['HTTP_REFERER'] ?? null,
    $body,
    $_GET,
    $_POST,
    $_FILES,
    $_COOKIE,
    $_SERVER
);

try {
    Core::getRouter()->handleRequest($request);
} catch (Throwable $t) {
    // Return a 500 Internal Error as fallback
    Core::getDefaultLogger()->logError(
        'Index error' .
        ' - Error: ' . $t->getMessage() .
        ' - Trace: ' . $t->getTraceAsString()
    );

    header('HTTP/1.1 500 Internal Server Error');
    echo 'There was an unexpected error :(';
}
