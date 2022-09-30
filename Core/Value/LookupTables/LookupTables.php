<?php

namespace Amora\Core\Value;

use Amora\Core\Core;

require_once Core::getPathRoot() . '/Core/Module/Article/DataLayer/ArticleDataLayer.php';
require_once Core::getPathRoot() . '/Core/Module/Article/DataLayer/MediaDataLayer.php';
require_once Core::getPathRoot() . '/Core/Module/Stats/DataLayer/StatsDataLayer.php';
require_once Core::getPathRoot() . '/Core/Module/User/DataLayer/UserDataLayer.php';
require_once Core::getPathRoot() . '/Core/Module/User/Value/UserJourneyStatus.php';
require_once Core::getPathRoot() . '/Core/Module/User/Value/UserRole.php';
require_once Core::getPathRoot() . '/Core/Module/User/Value/VerificationType.php';
require_once Core::getPathRoot() . '/Core/Module/Article/Value/ArticleType.php';
require_once Core::getPathRoot() . '/Core/Module/Article/Value/ArticleStatus.php';
require_once Core::getPathRoot() . '/Core/Module/Article/Value/ArticleSectionType.php';
require_once Core::getPathRoot() . '/Core/Module/Article/Value/MediaType.php';
require_once Core::getPathRoot() . '/Core/Module/Article/Value/MediaStatus.php';
require_once Core::getPathRoot() . '/Core/Module/Stats/Value/EventType.php';

use Amora\Core\Entity\Util\LookupTableSettings;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\Article\DataLayer\ArticleDataLayer;
use Amora\Core\Module\Article\DataLayer\MediaDataLayer;
use Amora\Core\Module\Article\Value\ArticleSectionType;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\Stats\DataLayer\StatsDataLayer;
use Amora\Core\Module\Stats\StatsCore;
use Amora\Core\Module\Stats\Value\EventType;
use Amora\Core\Module\User\DataLayer\UserDataLayer;
use Amora\Core\Module\User\UserCore;
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
        database: UserCore::getDb(),
        tableName: UserDataLayer::USER_ROLE_TABLE,
        tableFieldsToValues: asArray(UserRole::getAll()),
    ),
    new LookupTableSettings(
        database: ArticleCore::getDb(),
        tableName: ArticleDataLayer::ARTICLE_TYPE_TABLE,
        tableFieldsToValues: asArray(ArticleType::getAll()),
    ),
    new LookupTableSettings(
        database: ArticleCore::getDb(),
        tableName: ArticleDataLayer::ARTICLE_STATUS_TABLE,
        tableFieldsToValues: asArray(ArticleStatus::getAll()),
    ),
    new LookupTableSettings(
        database: ArticleCore::getDb(),
        tableName: ArticleDataLayer::ARTICLE_SECTION_TYPE_TABLE,
        tableFieldsToValues: asArray(ArticleSectionType::getAll()),
    ),
    new LookupTableSettings(
        database: UserCore::getDb(),
        tableName: UserDataLayer::USER_JOURNEY_STATUS_TABLE,
        tableFieldsToValues: asArray(UserJourneyStatus::getAll()),
    ),
    new LookupTableSettings(
        database: UserCore::getDb(),
        tableName: UserDataLayer::USER_VERIFICATION_TYPE_TABLE,
        tableFieldsToValues: asArray(VerificationType::getAll()),
    ),
    new LookupTableSettings(
        database: ArticleCore::getDb(),
        tableName: MediaDataLayer::MEDIA_STATUS_TABLE_NAME,
        tableFieldsToValues: asArray(MediaStatus::getAll()),
    ),
    new LookupTableSettings(
        database: ArticleCore::getDb(),
        tableName: MediaDataLayer::MEDIA_TYPE_TABLE_NAME,
        tableFieldsToValues: asArray(MediaType::getAll()),
    ),
    new LookupTableSettings(
        database: StatsCore::getDb(),
        tableName: StatsDataLayer::EVENT_TYPE_TABLE,
        tableFieldsToValues: asArray(EventType::getAll()),
    ),
];
