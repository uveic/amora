<?php

namespace Amora\Core\Module\User\Value;

use Amora\Core\Model\Util\LookupTableBasicValue;

final class UserJourneyStatus
{
    const USER_CREATED_PENDING_PASSWORD = 500;
    const REGISTRATION = 1000;

    public static function getAll(): array
    {
        return [
            self::USER_CREATED_PENDING_PASSWORD => new LookupTableBasicValue(
                self::USER_CREATED_PENDING_PASSWORD,
                'Pending Password'
            ),
            self::REGISTRATION => new LookupTableBasicValue(self::REGISTRATION, 'Registered'),
        ];
    }

    public static function asArray(): array
    {
        $output = [];
        foreach (self::getAll() as $item) {
            $output[] = $item->asArray();
        }
        return $output;
    }

    public static function getNameForId(int $id): string
    {
        $all = self::getAll();
        return isset($all[$id]) ? $all[$id]->getName() : 'Unknown';
    }

    public static function getInitialJourneyIdFromRoleId(int $roleId): int
    {
        switch ($roleId) {
            case UserRole::ADMIN:
            case UserRole::USER:
                return self::USER_CREATED_PENDING_PASSWORD;
        }

        return self::REGISTRATION;
    }
}
