<?php

namespace Amora\App\Value;

use Amora\Core\Core;

require_once Core::getPathRoot() . '/Core/Module/Mailer/Value/MailerTemplate.php';
require_once Core::getPathRoot() . '/App/Value/Mailer/AppMailerTemplate.php';

use Amora\App\Value\Mailer\AppMailerTemplate;
use Amora\Core\Model\Util\LookupTableSettings;
use function Amora\Core\Value\asArray;

return [
    new LookupTableSettings(
        database: Core::getMailerDb(),
        tableName: 'mailer_template',
        tableFieldsToValues: asArray(AppMailerTemplate::getAll()),
    ),
];
