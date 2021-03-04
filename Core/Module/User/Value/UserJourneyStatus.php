<?php

namespace Amora\Core\Module\User\Value;

use Amora\Core\Model\Util\LookupTableBasicValue;

final class UserJourneyStatus
{
    const REGISTRATION = 1000;

    public static function getAll(): array
    {
        return [
            self::REGISTRATION => new LookupTableBasicValue(self::REGISTRATION, 'User registered'),
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
            case UserRole::CUSTOMER:
                return self::REGISTRATION;
        }

        return self::REGISTRATION;
    }
}
