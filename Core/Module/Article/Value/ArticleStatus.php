<?php

namespace Amora\Core\Module\Article\Value;

enum ArticleStatus: int
{
    case Published = 1;
    case Deleted = 2;
    case Draft = 3;
    case Private = 4;
    case Unlisted = 5;

    public static function getAll(): array
    {
        return [
            self::Published,
            self::Private,
            self::Unlisted,
            self::Draft,
            self::Deleted,
        ];
    }

    public static function isPublic(self $item): bool
    {
        return match ($item) {
            self::Published, self::Private, self::Unlisted => true,
            default => false,
        };
    }
}
