<?php

namespace Amora\Core\Module\User\Value;

use Amora\Core\Model\Util\LookupTableBasicValue;

final class VerificationType
{
    const ACCOUNT = 1;
    const PASSWORD_RESET = 2;
    const UPDATE_EMAIL_ADDRESS = 3;

    public static function getAll(): array
    {
        return [
            self::ACCOUNT => new LookupTableBasicValue(self::ACCOUNT, 'Account Verification'),
            self::PASSWORD_RESET => new LookupTableBasicValue(self::PASSWORD_RESET, 'Password Reset'),
            self::UPDATE_EMAIL_ADDRESS => new LookupTableBasicValue(self::UPDATE_EMAIL_ADDRESS, 'Update email address'),
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

    public static function getNameForId(int $id): string
    {
        $all = self::getAll();
        return empty($all[$id]) ? 'Unknown' : $all[$id]->getName();
    }
}
