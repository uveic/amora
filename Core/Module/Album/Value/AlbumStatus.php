<?php

namespace Amora\Core\Module\Album\Value;

enum AlbumStatus: int
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
}
