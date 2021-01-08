<?php

namespace uve\core\module\mailer\value;

final class MailerTemplate
{
    const INVITATION_001 = 1000;

    const ACCOUNT_VERIFICATION = 2000;

    public static function getAll(): array
    {
        return [
            ['id' => self::INVITATION_001,                'name' => 'Invitation 001'],

            ['id' => self::ACCOUNT_VERIFICATION,          'name' => 'Account Verification'],
        ];
    }
}
