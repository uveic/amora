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

use Amora\Core\Model\Util\LookupTableSettings;
use Amora\Core\Module\Article\Value\ArticleSectionType;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\Mailer\Value\MailerTemplate;
use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Module\User\Value\VerificationType;

return [
    new LookupTableSettings(
        database: Core::getCoreDb(),
        tableName: 'language',
        tableFieldsToValues: Language::getAll(),
    ),
    new LookupTableSettings(
        database: Core::getCoreDb(),
        tableName: 'user_role',
        tableFieldsToValues: UserRole::asArray(),
    ),
    new LookupTableSettings(
        database: Core::getCoreDb(),
        tableName: 'article_type',
        tableFieldsToValues: ArticleType::asArray(),
    ),
    new LookupTableSettings(
        database: Core::getCoreDb(),
        tableName: 'article_status',
        tableFieldsToValues: ArticleStatus::asArray(),
    ),
    new LookupTableSettings(
        database: Core::getCoreDb(),
        tableName: 'article_section_type',
        tableFieldsToValues: ArticleSectionType::asArray(),
    ),
    new LookupTableSettings(
        database: Core::getCoreDb(),
        tableName: 'user_journey_status',
        tableFieldsToValues: UserJourneyStatus::asArray(),
    ),
    new LookupTableSettings(
        database: Core::getCoreDb(),
        tableName: 'user_verification_type',
        tableFieldsToValues: VerificationType::asArray(),
    ),

    new LookupTableSettings(
        database: Core::getMailerDb(),
        tableName: 'mailer_template',
        tableFieldsToValues: MailerTemplate::asArray(),
    ),
];
