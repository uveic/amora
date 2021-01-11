#!/usr/bin/env php
<?php

namespace uve\app\bin\core;

use Throwable;
use uve\core\module\article\value\ArticleSectionType;
use uve\core\module\mailer\value\MailerTemplate;
use uve\core\module\user\value\UserJourneyStatus;
use uve\core\Core;
use uve\core\module\user\value\VerificationType;
use uve\value\Language;
use uve\core\module\article\value\ArticleStatus;
use uve\core\module\article\value\ArticleType;
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

require_once Core::getPathRoot() . '/value/language/Language.php';
require_once Core::getPathRoot() . '/core/module/user/value/UserJourneyStatus.php';
require_once Core::getPathRoot() . '/core/module/user/value/UserRole.php';
require_once Core::getPathRoot() . '/core/module/user/value/VerificationType.php';
require_once Core::getPathRoot() . '/core/module/article/value/ArticleType.php';
require_once Core::getPathRoot() . '/core/module/article/value/ArticleStatus.php';
require_once Core::getPathRoot() . '/core/module/article/value/ArticleSectionType.php';
require_once Core::getPathRoot() . '/core/module/mailer/value/MailerTemplate.php';

///////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////
/// Lookup tables configuration


$lookupTables = [
    [
        'table_fields_to_values' => array_values(Language::getAll()),
        'table_name' => 'language',
        'db' => Core::getCoreDb()
    ],
    [
        'table_fields_to_values' => array_values(UserRole::getAll()),
        'table_name' => 'user_role',
        'db' => Core::getCoreDb()
    ],
    [
        'table_fields_to_values' => array_values(ArticleType::getAll()),
        'table_name' => 'article_type',
        'db' => Core::getCoreDb()
    ],
    [
        'table_fields_to_values' => array_values(ArticleStatus::getAll()),
        'table_name' => 'article_status',
        'db' => Core::getCoreDb()
    ],
    [
        'table_fields_to_values' => array_values(UserJourneyStatus::getAll()),
        'table_name' => 'user_journey_status',
        'db' => Core::getCoreDb()
    ],
    [
        'table_fields_to_values' => array_values(MailerTemplate::getAll()),
        'table_name' => 'mailer_template',
        'db' => Core::getMailerDb()
    ],
    [
        'table_fields_to_values' => array_values(VerificationType::getAll()),
        'table_name' => 'user_verification_type',
        'db' => Core::getCoreDb()
    ],
    [
        'table_fields_to_values' => array_values(ArticleSectionType::getAll()),
        'table_name' => 'article_section_type',
        'db' => Core::getCoreDb()
    ]
];


///
///////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////


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
