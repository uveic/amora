<?php

namespace Amora\Core\Module\User\Value;

enum UserActionType: int
{
    case Create = 1;
    case Update = 2;
    case UpdateStatus = 3;
    case UpdateRole = 4;
    case PasswordCreation = 5;
    case UpdatePassword = 6;
    case UpdateEmailRequest = 7;
    case VerifyEmail = 8;

    public static function getAll(): array
    {
        return [
            self::Create,
            self::Update,
            self::UpdateStatus,
            self::UpdateRole,
            self::PasswordCreation,
            self::UpdatePassword,
            self::UpdateEmailRequest,
            self::VerifyEmail,
        ];
    }
}
