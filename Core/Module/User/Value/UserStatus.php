<?php

namespace Amora\Core\Module\User\Value;

use Amora\Core\Value\CoreIcons;

enum UserStatus: int
{
    case Enabled = 1;
    case Disabled = 2;

    public static function getAll(): array
    {
        return [
            self::Enabled,
            self::Disabled,
        ];
    }

    public function getClassname(): string
    {
        return match ($this) {
            UserStatus::Enabled => 'status-published',
            UserStatus::Disabled => 'status-deleted',
        };
    }

    public function getName(): string
    {
        return match ($this) {
            self::Enabled => 'Activo',
            self::Disabled => 'Suspendido',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Enabled => CoreIcons::EYE,
            self::Disabled => CoreIcons::EYE_CLOSED,
        };
    }
}
