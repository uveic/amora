<?php

namespace Amora\Core\Value;

use Amora\Core\Core;

require_once Core::getPathRoot() . '/Core/Module/Article/DataLayer/ArticleDataLayer.php';
require_once Core::getPathRoot() . '/Core/Module/Article/DataLayer/MediaDataLayer.php';
require_once Core::getPathRoot() . '/Core/Module/Analytics/DataLayer/AnalyticsDataLayer.php';
require_once Core::getPathRoot() . '/Core/Module/User/DataLayer/UserDataLayer.php';
require_once Core::getPathRoot() . '/Core/Module/User/Value/UserJourneyStatus.php';
require_once Core::getPathRoot() . '/Core/Module/User/Value/UserStatus.php';
require_once Core::getPathRoot() . '/Core/Module/User/Value/UserRole.php';
require_once Core::getPathRoot() . '/Core/Module/User/Value/VerificationType.php';
require_once Core::getPathRoot() . '/Core/Module/Article/Value/ArticleType.php';
require_once Core::getPathRoot() . '/Core/Module/Article/Value/ArticleStatus.php';
require_once Core::getPathRoot() . '/Core/Module/Article/Value/ArticleSectionType.php';
require_once Core::getPathRoot() . '/Core/Module/Article/Value/MediaType.php';
require_once Core::getPathRoot() . '/Core/Module/Article/Value/MediaStatus.php';
require_once Core::getPathRoot() . '/Core/Module/Analytics/Value/EventType.php';
require_once Core::getPathRoot() . '/Core/Module/Album/Value/AlbumStatus.php';
require_once Core::getPathRoot() . '/Core/Module/Album/Value/Template.php';

use Amora\Core\Entity\Util\LookupTableSettings;
use Amora\Core\Module\Album\AlbumCore;
use Amora\Core\Module\Album\Datalayer\AlbumDataLayer;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Module\Album\Value\Template;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\Article\DataLayer\ArticleDataLayer;
use Amora\Core\Module\Article\DataLayer\MediaDataLayer;
use Amora\Core\Module\Article\Value\ArticleSectionType;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\Analytics\DataLayer\AnalyticsDataLayer;
use Amora\Core\Module\Analytics\AnalyticsCore;
use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Module\User\DataLayer\UserDataLayer;
use Amora\Core\Module\User\UserCore;
use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Module\User\Value\UserStatus;
use Amora\Core\Module\User\Value\VerificationType;
use BackedEnum;

function asArray(array $values): array
{
    $output = [];
    /** @var BackedEnum $value */
    foreach ($values as $value) {
        $name = method_exists($value, 'getName') ? $value->getName($value) : $value->name;
        $output[] = [
            'id' => $value->value,
            'name' => $name,
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
        tableName: UserDataLayer::USER_STATUS_TABLE,
        tableFieldsToValues: asArray(UserStatus::getAll()),
    ),
    new LookupTableSettings(
        database: UserCore::getDb(),
        tableName: UserDataLayer::USER_VERIFICATION_TYPE_TABLE,
        tableFieldsToValues: asArray(VerificationType::getAll()),
    ),
    new LookupTableSettings(
        database: ArticleCore::getDb(),
        tableName: MediaDataLayer::MEDIA_STATUS_TABLE,
        tableFieldsToValues: asArray(MediaStatus::getAll()),
    ),
    new LookupTableSettings(
        database: ArticleCore::getDb(),
        tableName: MediaDataLayer::MEDIA_TYPE_TABLE,
        tableFieldsToValues: asArray(MediaType::getAll()),
    ),
    new LookupTableSettings(
        database: AnalyticsCore::getDb(),
        tableName: AnalyticsDataLayer::EVENT_TYPE_TABLE,
        tableFieldsToValues: asArray(EventType::getAll()),
    ),
    new LookupTableSettings(
        database: AlbumCore::getDb(),
        tableName: AlbumDataLayer::ALBUM_STATUS_TABLE,
        tableFieldsToValues: asArray(AlbumStatus::getAll()),
    ),
    new LookupTableSettings(
        database: AlbumCore::getDb(),
        tableName: AlbumDataLayer::ALBUM_TEMPLATE_TABLE,
        tableFieldsToValues: asArray(Template::getAll()),
    ),
];
