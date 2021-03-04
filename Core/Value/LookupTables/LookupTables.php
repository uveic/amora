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
    new LookupTableSettings(Core::getCoreDb(), 'language', Language::getAll()),
    new LookupTableSettings(Core::getCoreDb(), 'user_role', UserRole::asArray()),
    new LookupTableSettings(Core::getCoreDb(), 'article_type', ArticleType::asArray()),
    new LookupTableSettings(Core::getCoreDb(), 'article_status', ArticleStatus::asArray()),
    new LookupTableSettings(
        Core::getCoreDb(),
        'article_section_type',
        ArticleSectionType::asArray()
    ),
    new LookupTableSettings(Core::getCoreDb(), 'user_journey_status', UserJourneyStatus::asArray()),
    new LookupTableSettings(
        Core::getCoreDb(),
        'user_verification_type',
        VerificationType::asArray()
    ),

    new LookupTableSettings(Core::getMailerDb(), 'mailer_template', MailerTemplate::asArray()),
];
