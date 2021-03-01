<?php

namespace Amora\Core\Module\User\Value;

final class VerificationType
{
    const ACCOUNT = 1;
    const PASSWORD_RESET = 2;
    const UPDATE_EMAIL_ADDRESS = 3;

    public static function getAll(): array
    {
        return [
            self::ACCOUNT => [
                'id' => self::ACCOUNT,
                'name' => 'Account Verification'
            ],
            self::PASSWORD_RESET => [
                'id' => self::PASSWORD_RESET,
                'name' => 'Password Reset'
            ],
            self::UPDATE_EMAIL_ADDRESS => [
                'id' => self::UPDATE_EMAIL_ADDRESS,
                'name' => 'Update email address'
            ]
        ];
    }

    public static function getNameForId(int $id): string
    {
        $all = self::getAll();
        return empty($all[$id]) ? 'Unknown' : $all[$id]['name'];
    }
}
