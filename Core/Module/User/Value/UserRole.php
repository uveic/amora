<?php

namespace Amora\Core\Module\User\Value;

enum UserRole: int
{
    case Admin = 1;
    case User = 10;

    public static function getAll(): array
    {
        return [
            self::Admin,
            self::User,
        ];
    }
}
