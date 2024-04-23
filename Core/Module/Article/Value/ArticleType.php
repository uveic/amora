<?php

namespace Amora\Core\Module\Article\Value;

enum ArticleType: int
{
    case Blog = 3;
    case Page = 4;

    public static function getAll(): array
    {
        return [
            self::Blog,
            self::Page,
        ];
    }
}
