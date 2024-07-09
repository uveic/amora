<?php

namespace Amora\App\Value;

use Amora\Core\Module\Mailer\Value\MailerTemplate;

enum AppMailerTemplate: int
{
    public static function getAll(): array
    {
        return array_merge(
            MailerTemplate::getAll(),
            [],
        );
    }
}
