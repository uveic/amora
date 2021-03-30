<?php

namespace Amora\Core\Module\User\Value;

use Amora\Core\Model\Util\LookupTableBasicValue;

final class VerificationType
{
    const EMAIL_ADDRESS = 1;
    const PASSWORD_RESET = 2;
    const PASSWORD_CREATION = 3;

    public static function getAll(): array
    {
        return [
            self::EMAIL_ADDRESS => new LookupTableBasicValue(self::EMAIL_ADDRESS, 'Email Address Verification'),
            self::PASSWORD_RESET => new LookupTableBasicValue(self::PASSWORD_RESET, 'Password Reset'),
            self::PASSWORD_CREATION => new LookupTableBasicValue(self::PASSWORD_CREATION, 'Create password for new user'),
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
