<?php

namespace Amora\App\Value;

use Amora\Core\Core;

require_once Core::getPathRoot() . '/Core/Module/Article/DataLayer/ArticleDataLayer.php';
require_once Core::getPathRoot() . '/Core/Module/Mailer/Value/MailerTemplate.php';
require_once Core::getPathRoot() . '/App/Value/AppMailerTemplate.php';
require_once Core::getPathRoot() . '/Core/Module/Article/Value/PageContentType.php';
require_once Core::getPathRoot() . '/App/Value/AppPageContentType.php';

use Amora\Core\Entity\Util\LookupTableSettings;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\Article\Datalayer\ArticleDataLayer;
use function Amora\Core\Value\asArray;

return [
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
