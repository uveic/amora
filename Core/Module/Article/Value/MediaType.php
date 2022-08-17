<?php

namespace Amora\Core\Module\Article\Value;

enum MediaType: int
{
    case PDF = 1;
    case Image = 2;

    public static function getAll(): array
    {
        return [
            self::PDF,
            self::Image,
        ];
    }
}
