<?php

namespace Amora\Core\Module\Article\Value;

enum PageContentType: int
{
    case Homepage = 1;
    case BlogBottom = 2;

    public static function getAll(): array
    {
        return [
            self::Homepage,
            self::BlogBottom,
        ];
    }
}
