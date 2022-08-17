<?php

namespace Amora\Core\Module\Article\Value;

enum MediaStatus: int
{
    case Active = 1;
    case Deleted = 2;

    public static function getAll(): array
    {
        return [
            self::Active,
            self::Deleted,
        ];
    }
}
