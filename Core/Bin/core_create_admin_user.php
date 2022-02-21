#!/usr/bin/env php
<?php

namespace Amora\Core\Bin;

use DateTimeImmutable;
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

foreach ($argv as $item) {
    if (str_starts_with($item, '--pass=')) {
        $pass = substr($item, 7, strlen($item) - 7);
        continue;
    }

    if (str_starts_with($item, '--password=')) {
        $pass = substr($item, 11, strlen($item) - 11);
    }

    if (str_starts_with($item, '--email=')) {
        $email = substr($item, 8, strlen($item) - 8);
    }

    if (str_starts_with($item, '--name=')) {
        $name = substr($item, 7, strlen($item) - 7);
    }
}

if (empty($email) || !StringUtil::isEmailAddressValid($email)) {
    $logger->logError('Email address not valid (' . $email . '). Aborting...');
    exit;
}

if (empty($pass)) {
    $pass = StringUtil::getRandomString(10);
}

$now = new DateTimeImmutable();
$res = UserCore::getUserService()->storeUser(
    new User(
        id: null,
        languageId: Language::ENGLISH,
        role: UserRole::Admin,
        journeyStatus: UserJourneyStatus::Registration,
        createdAt: $now,
        updatedAt: $now,
        email: $email,
        name: $name,
        passwordHash: StringUtil::hashPassword($pass),
        bio: null,
        isEnabled: true,
        verified: true,
        timezone: DateUtil::convertStringToDateTimeZone('Europe/Madrid'),
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
