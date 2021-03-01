#!/usr/bin/env php
<?php

namespace Amora\App\Bin;

use Throwable;
use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Core;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\UserCore;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;
use Amora\Core\Value\Language;
use Amora\Core\Module\User\Value\UserRole;

// change working directory
chdir(dirname(__FILE__));

require_once '../Core.php';

try {
    Core::initiate(realpath(__DIR__ . '/../..'));
} catch (Throwable $t) {
    echo 'Error initiating application: ' . $t->getMessage() . ' ## Aborting...';
    exit;
}

require_once Core::getPathRoot() . '/Core/Module/User/Value/UserJourneyStatus.php';
require_once Core::getPathRoot() . '/Core/Module/User/Value/UserRole.php';
require_once Core::getPathRoot() . '/Core/Util/DateUtil.php';

$logger = Core::getLogger('BinCoreCreateAdminUser');

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
