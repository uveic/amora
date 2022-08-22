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

    public static function getIcon(
        self $item,
        string $class = '',
        bool $white = false,
        ?string $alt = null,
    ): string {
        $class = $class ? ' ' . $class : '';
        $white = $white ? '-white' : '';
        return match($item) {
            self::Blog => '<img class="img-svg' . $class . '" width="20" height="20" src="/img/svg/article-medium' . $white . '.svg" alt="' . $alt . '">',
            self::Page => '<img class="img-svg' . $class . '" width="20" height="20" src="/img/svg/note-pencil' . $white . '.svg" alt="' . $alt . '">',
            default => '',
        };
    }
}
