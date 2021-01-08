<?php

namespace uve\core\module\user\value;

final class UserJourneyStatus
{
    const REGISTRATION = 1000;

    public static function getAll(): array
    {
        return [
            self::REGISTRATION => [
                'id' => self::REGISTRATION,
                'name' => 'User registered'
            ]
        ];
    }

    public static function getNameForId(int $id): string
    {
        $all = self::getAll();
        return $all[$id]['name'] ?? 'Unknown';
    }

    public static function getInitialJourneyIdFromRoleId(int $roleId): int
    {
        switch ($roleId) {
            case UserRole::ADMIN:
            case UserRole::CUSTOMER:
                return self::REGISTRATION;
        }

        return self::REGISTRATION;
    }
}
