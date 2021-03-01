<?php

namespace Amora\Core\Module\User\Value;

final class UserRole
{
    const ADMIN = 1;
    const CUSTOMER = 10;

    public static function getAll(): array
    {
        return [
            self::ADMIN => [
                'id' => self::ADMIN,
                'name' => 'Admin'
            ],
            self::CUSTOMER => [
                'id' => self::CUSTOMER,
                'name' => 'Customer'
            ]
        ];
    }

    public static function getUserRoleIdsWithAdminPermissions(): array
    {
        return [
            self::ADMIN
        ];
    }

    public static function getNameForId(int $id): string
    {
        $all = self::getAll();
        return empty($all[$id]) ? 'Unknown' : $all[$id]['name'];
    }
}
