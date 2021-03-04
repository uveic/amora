<?php

namespace Amora\Core\Module\Mailer\Value;

use Amora\Core\Model\Util\LookupTableBasicValue;

final class MailerTemplate
{
    const INVITATION_001 = 1000;

    const ACCOUNT_VERIFICATION = 2000;

    public static function getAll(): array
    {
        return [
            self::INVITATION_001 => new LookupTableBasicValue(
                self::INVITATION_001,
                'Invitation 001'
            ),
            self::ACCOUNT_VERIFICATION => new LookupTableBasicValue(
                self::ACCOUNT_VERIFICATION,
                'Account Verification'
            ),
        ];
    }

    public static function asArray(): array
    {
        $output = [];
        foreach (self::getAll() as $item) {
            $output[] = $item->asArray();
        }
        return $output;
    }
}
