<?php

namespace Amora\Core\Module\Article\Value;

enum ArticleType: int
{
    case Homepage = 1;
    case Blog = 3;
    case Page = 4;

    public static function getAll(): array
    {
        return [
            self::Homepage,
            self::Blog,
            self::Page,
        ];
    }
}
