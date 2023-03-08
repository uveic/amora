<?php

namespace Amora\Core\Module\User\Value;

enum UserStatus: int
{
    case Enabled = 1;
    case Disabled = 2;

    public static function getAll(): array
    {
        return [
            self::Enabled,
            self::Disabled,
        ];
    }
}
