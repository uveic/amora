<?php

namespace Amora\Core\Module\Article\Value;

enum ArticleType: int
{
    const Homepage = 1;
    const Blog = 3;
    const Page = 4;

    public static function getAll(): array
    {
        return [
            self::Homepage,
            self::Blog,
            self::Page,
        ];
    }
}
