<?php

use Amora\Core\Core;
use Amora\Core\Entity\Request;
use Amora\Core\Util\NetworkUtil;

require_once '../Core/Core.php';
try {
    Core::initiate(realpath(__DIR__ . '/..'));
} catch (Throwable $t) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'There was an unexpected error :(';
    exit;
}

require_once Core::getPathRoot() . '/vendor/autoload.php';

$body = file_get_contents('php://input');
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), " /");
$path = empty($path) ? 'home' : $path;

$request = new Request(
    sourceIp: NetworkUtil::determineClientIp(),
    userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null,
    method: strtoupper($_SERVER['REQUEST_METHOD']),
    path: $path,
    referrer: $_SERVER['HTTP_REFERER'] ?? null,
    body: $body,
    getParams: $_GET,
    postParams: $_POST,
    files: $_FILES,
    cookies: $_COOKIE,
    headers: $_SERVER,
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
