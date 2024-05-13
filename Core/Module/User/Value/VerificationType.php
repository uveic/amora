<?php

namespace Amora\Core\Module\User\Value;

enum VerificationType: int
{
    const RESET_LINK_VALID_FOR_SECONDS = 7200;

    case EmailAddress = 1;
    case PasswordReset = 2;
    case PasswordCreation = 3;

    public static function getAll(): array
    {
        return [
            self::EmailAddress,
            self::PasswordReset,
            self::PasswordCreation,
        ];
    }
}
