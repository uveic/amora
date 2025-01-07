<?php

namespace Amora\Core\Module\Album\Value;

use Amora\Core\Value\CoreIcons;

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

    public function isPublished(): bool
    {
        return match ($this) {
            self::Published, self::Private, self::Unlisted => true,
            default => false,
        };
    }

    public function isPublic(): bool
    {
        return match ($this) {
            self::Published, self::Unlisted => true,
            default => false,
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Published => CoreIcons::CHECKS,
            self::Deleted => CoreIcons::FILE_X,
            self::Draft => CoreIcons::FILE_IMAGE,
            self::Private, self::Unlisted => CoreIcons::CHECK,
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
