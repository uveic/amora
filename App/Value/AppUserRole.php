<?php

namespace Amora\App\Value;

use Amora\Core\Core;
use Amora\Core\Module\User\Value\UserRole;

enum AppUserRole: int
{
    public static function getAll(bool $includeAdmin = true): array
    {
        $output = [
            UserRole::User,
        ];

        if ($includeAdmin) {
            $output[] = UserRole::Admin;
        }

        return $output;
    }

    public function getTitle(Language $language): string
    {
        return Core::getLocalisationUtil($language)->getValue('userRole' . $this->name);
    }

    public function getClass(): string
    {
        return '';
    }

    public function getIcon(): string
    {
        return '';
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
