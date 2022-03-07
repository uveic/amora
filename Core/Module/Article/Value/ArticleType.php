<?php

namespace Amora\Core\Module\Article\Value;

enum ArticleType: int
{
    case PartialContentHomepage = 1;
    case Blog = 3;
    case Page = 4;

    case PartialContentBlogBottom = 100;

    public static function getAll(): array
    {
        return [
            self::PartialContentHomepage,
            self::Blog,
            self::Page,
            self::PartialContentBlogBottom,
        ];
    }

    public static function isPartialContent(?self $item): bool
    {
        if (empty($item)) {
            return false;
        }

        return match ($item) {
            self::PartialContentHomepage, self::PartialContentBlogBottom => true,
            default => false,
        };
    }
}
