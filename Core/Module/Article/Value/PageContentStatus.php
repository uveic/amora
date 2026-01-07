<?php

namespace Amora\Core\Module\Article\Value;

use Amora\Core\Value\CoreIcons;

enum PageContentStatus: int
{
    case Published = 1;
    case Draft = 2;
    case Deleted = 3;

    public static function getAll(): array
    {
        return [
            self::Published,
            self::Draft,
            self::Deleted,
        ];
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Published => CoreIcons::CHECKS,
            self::Draft => CoreIcons::SPARKLE,
            self::Deleted => CoreIcons::TRASH,
        };
    }

    public function getClass(): string
    {
        return match ($this) {
            self::Published => 'status-published',
            self::Draft => 'status-draft',
            self::Deleted => 'status-deleted',
        };
    }
}
