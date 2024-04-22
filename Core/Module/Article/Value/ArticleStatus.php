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

    public function isPublic(): bool
    {
        return match ($this) {
            self::Published, self::Private, self::Unlisted => true,
            default => false,
        };
    }

    public function getClass(): string
    {
        return match ($this) {
            self::Published => 'status-published',
            self::Private, self::Unlisted => 'status-private',
            self::Deleted => 'status-deleted',
            self::Draft => 'status-draft',
        };
    }
}
