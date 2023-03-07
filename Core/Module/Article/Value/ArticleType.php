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
