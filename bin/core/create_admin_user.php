#!/usr/bin/env php
<?php

namespace uve\app\bin\core;

use Throwable;
use uve\core\module\user\value\UserJourneyStatus;
use uve\core\Core;
use uve\core\module\user\model\User;
use uve\core\module\user\UserCore;
use uve\core\util\DateUtil;
use uve\core\util\StringUtil;
use uve\value\Language;
use uve\core\module\user\value\UserRole;

// change working directory
chdir(dirname(__FILE__));

require_once '../../core/Core.php';

try {
    Core::initiate(realpath(__DIR__ . '/../..'));
} catch (Throwable $t) {
    echo 'Error initiating application: ' . $t->getMessage() . ' ## Aborting...';
    exit;
}

require_once Core::getPathRoot() . '/core/module/user/value/UserJourneyStatus.php';
require_once Core::getPathRoot() . '/core/module/user/value/UserRole.php';
require_once Core::getPathRoot() . '/core/util/DateUtil.php';
require_once Core::getPathRoot() . '/value/language/Language.php';

$logger = Core::getLogger('create_user');

$logger->logInfo('Creating user...');

$pass = null;
$email = null;
$name = null;

if (empty($argv[1])) {
    $logger->logError(
        'Required arguments: --email. Optional: --name, --pass. Aborting...'
    );

    exit;
}

if (!empty($argv[1])) {
    foreach ($argv as $item) {
        if (substr($item, 0, 7) === '--pass=') {
            $pass = substr($item, 7, strlen($item) - 7);
            continue;
        }

        if (substr($item, 0, 11) === '--password=') {
            $pass = substr($item, 11, strlen($item) - 11);
        }

        if (substr($item, 0, 8) === '--email=') {
            $email = substr($item, 8, strlen($item) - 8);
        }

        if (substr($item, 0, 7) === '--name=') {
            $name = substr($item, 7, strlen($item) - 7);
        }
    }
}

if (empty($email) || !StringUtil::isEmailAddressValid($email)) {
    $logger->logError('Email address not valid (' . $email . '). Aborting...');
    exit;
}

if (empty($pass)) {
    $pass = StringUtil::getRandomString(10);
}

UserCore::getDb()->withTransaction(function() use ($logger, $email, $name, $pass) {
    $now = DateUtil::getCurrentDateForMySql();
    $res = UserCore::getUserService()->storeUser(
        new User(
            null,
            Language::ENGLISH,
            UserRole::ADMIN,
            UserJourneyStatus::getInitialJourneyIdFromRoleId(UserRole::ADMIN),
            $now,
            $now,
            $email,
            $name,
            StringUtil::hashPassword($pass),
            null,
            true,
            true,
            'Europe/Madrid'
        )
    );

    if (empty($res)) {
        $logger->logError(
            'User not created. Error: ' .
            (empty($res['errorMessage']) ? 'UNKNOWN' : $res['errorMessage'])
        );
        exit;
    }

    $logger->logInfo('User created: ' . $email . ' / ' . $pass);

    return ['success' => true];
});
