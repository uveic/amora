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
use BackedEnum;

function asArray(array $values): array
{
    $output = [];
    /** @var BackedEnum $value */
    foreach ($values as $value) {
        $output[] = [
            'id' => $value->value,
            'name' => $value->name,
        ];
    }

    return $output;
}


return [
    new LookupTableSettings(
        database: Core::getCoreDb(),
        tableName: 'language',
        tableFieldsToValues: Language::getAll(),
    ),
    new LookupTableSettings(
        database: Core::getCoreDb(),
        tableName: 'user_role',
        tableFieldsToValues: asArray(UserRole::getAll()),
    ),
    new LookupTableSettings(
        database: Core::getCoreDb(),
        tableName: 'article_type',
        tableFieldsToValues: asArray(ArticleType::getAll()),
    ),
    new LookupTableSettings(
        database: Core::getCoreDb(),
        tableName: 'article_status',
        tableFieldsToValues: asArray(ArticleStatus::getAll()),
    ),
    new LookupTableSettings(
        database: Core::getCoreDb(),
        tableName: 'article_section_type',
        tableFieldsToValues: asArray(ArticleSectionType::getAll()),
    ),
    new LookupTableSettings(
        database: Core::getCoreDb(),
        tableName: 'user_journey_status',
        tableFieldsToValues: asArray(UserJourneyStatus::getAll()),
    ),
    new LookupTableSettings(
        database: Core::getCoreDb(),
        tableName: 'user_verification_type',
        tableFieldsToValues: asArray(VerificationType::getAll()),
    ),

    new LookupTableSettings(
        database: Core::getMailerDb(),
        tableName: 'mailer_template',
        tableFieldsToValues: asArray(MailerTemplate::getAll()),
    ),
];
