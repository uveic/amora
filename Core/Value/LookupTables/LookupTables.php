<?php

namespace Amora\Core\Value;

use Amora\Core\Core;

require_once Core::getPathRoot() . '/Core/Module/User/Value/UserJourneyStatus.php';
require_once Core::getPathRoot() . '/Core/Module/User/Value/UserRole.php';
require_once Core::getPathRoot() . '/Core/Module/User/Value/VerificationType.php';
require_once Core::getPathRoot() . '/Core/Module/Article/Value/ArticleType.php';
require_once Core::getPathRoot() . '/Core/Module/Article/Value/ArticleStatus.php';
require_once Core::getPathRoot() . '/Core/Module/Article/Value/ArticleSectionType.php';
require_once Core::getPathRoot() . '/Core/Module/Mailer/Value/MailerTemplate.php';

use Amora\Core\Module\Article\Value\ArticleSectionType;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\Mailer\Value\MailerTemplate;
use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Module\User\Value\VerificationType;

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
        'table_fields_to_values' => array_values(ArticleType::asArray()),
        'table_name' => 'article_type',
        'db' => Core::getCoreDb()
    ],
    [
        'table_fields_to_values' => array_values(ArticleStatus::asArray()),
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
