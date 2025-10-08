<?php

namespace Amora\Core\Module\User\Value;

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Value\CoreIcons;

enum UserRole: int
{
    case Admin = 1;
    case User = 10;

    public static function getAll(): array
    {
        return [
            self::Admin,
            self::User,
        ];
    }

    public function getTitle(Language $language): string
    {
        $localisationUtil = Core::getLocalisationUtil($language);
        return $localisationUtil->getValue('userRole' . $this->name);
    }

    public function getClass(): string
    {
        return match($this) {
            self::Admin => 'status-private',
            self::User => 'status-draft',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::Admin => CoreIcons::CROWN,
            default => CoreIcons::USER,
        };
    }

    public function asHtml(Language $language): string
    {
        return '<span class="article-status icon-one-line ' .
            $this->getClass() . '">' .
            $this->getIcon() .
            $this->getTitle($language) .
            '</span>';
    }
}
