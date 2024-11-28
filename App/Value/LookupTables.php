<?php

namespace Amora\App\Value;

use Amora\Core\Core;
use Amora\Core\Entity\Util\LookupTableSettings;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\Article\Datalayer\ArticleDataLayer;
use Amora\Core\Module\User\Datalayer\UserDataLayer;
use Amora\Core\Module\User\UserCore;
use function Amora\Core\Value\asArray;

require_once Core::getPathRoot() . '/Core/Module/Mailer/Value/MailerTemplate.php';
require_once Core::getPathRoot() . '/App/Value/AppMailerTemplate.php';
require_once Core::getPathRoot() . '/Core/Module/Article/Value/PageContentType.php';
require_once Core::getPathRoot() . '/App/Value/AppPageContentType.php';
require_once Core::getPathRoot() . '/Core/Module/User/Value/UserRole.php';
require_once Core::getPathRoot() . '/App/Value/AppUserRole.php';

require_once Core::getPathRoot() . '/Core/Module/Article/DataLayer/ArticleDataLayer.php';
require_once Core::getPathRoot() . '/Core/Module/User/DataLayer/UserDataLayer.php';

return [
    new LookupTableSettings(
        database: UserCore::getDb(),
        tableName: UserDataLayer::USER_ROLE_TABLE,
        tableFieldsToValues: asArray(AppUserRole::getAll()),
    ),
    new LookupTableSettings(
        database: Core::getCoreDb(),
        tableName: 'core_language',
        tableFieldsToValues: asArray(Language::getAll()),
    ),
    new LookupTableSettings(
        database: Core::getMailerDb(),
        tableName: 'mailer_template',
        tableFieldsToValues: asArray(AppMailerTemplate::getAll()),
    ),
    new LookupTableSettings(
        database: ArticleCore::getDb(),
        tableName: ArticleDataLayer::CONTENT_TYPE_TABLE,
        tableFieldsToValues: asArray(AppPageContentType::getAll()),
    ),
];
