<?php

namespace uve\core\value;

use uve\core\Core;

require_once Core::getPathRoot() . '/core/value/language/Language.php';
require_once Core::getPathRoot() . '/core/module/user/value/UserJourneyStatus.php';
require_once Core::getPathRoot() . '/core/module/user/value/UserRole.php';
require_once Core::getPathRoot() . '/core/module/user/value/VerificationType.php';
require_once Core::getPathRoot() . '/core/module/article/value/ArticleType.php';
require_once Core::getPathRoot() . '/core/module/article/value/ArticleStatus.php';
require_once Core::getPathRoot() . '/core/module/article/value/ArticleSectionType.php';
require_once Core::getPathRoot() . '/core/module/mailer/value/MailerTemplate.php';

use uve\core\module\article\value\ArticleSectionType;
use uve\core\module\article\value\ArticleStatus;
use uve\core\module\article\value\ArticleType;
use uve\core\module\mailer\value\MailerTemplate;
use uve\core\module\user\value\UserJourneyStatus;
use uve\core\module\user\value\UserRole;
use uve\core\module\user\value\VerificationType;

return [
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
