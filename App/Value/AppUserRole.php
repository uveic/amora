<?php

namespace Amora\App\Value;

use Amora\Core\Module\User\Value\UserRole;

enum AppUserRole: int
{
    public static function getAll(): array
    {
        return array_merge(
            UserRole::getAll(),
            [],
        );
    }
}
