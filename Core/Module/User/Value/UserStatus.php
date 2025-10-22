<?php

namespace Amora\Core\Module\User\Value;

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Value\CoreIcons;

enum UserStatus: int
{
    case Enabled = 1;
    case Disabled = 2;
    case Deleted = 3;

    public static function getAll(): array
    {
        return [
            self::Enabled,
            self::Disabled,
            self::Deleted,
        ];
    }

    public function getClassname(): string
    {
        return match ($this) {
            self::Enabled => 'status-published',
            self::Disabled => 'status-disabled',
            self::Deleted => 'status-deleted',
        };
    }

    public function getTitle(Language $language): string
    {
        return Core::getLocalisationUtil($language)->getValue('userStatus' . $this->name);
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Enabled => CoreIcons::EYE,
            self::Disabled => CoreIcons::EYE_CLOSED,
            self::Deleted => CoreIcons::TRASH,
        };
    }

    public function asHtml(Language $language): string
    {
        return '<span class="article-status ' . $this->getClassname() . '">' . $this->getTitle($language) . '</span>';
    }

    public function isEnabled(): bool
    {
        return $this === self::Enabled;
    }
}
