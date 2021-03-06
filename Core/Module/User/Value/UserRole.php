<?php

namespace Amora\Core\Module\User\Value;

use Amora\Core\Model\Util\LookupTableBasicValue;

final class UserRole
{
    const ADMIN = 1;
    const USER = 10;

    public static function getAll(): array
    {
        return [
            self::ADMIN => new LookupTableBasicValue(self::ADMIN, 'Admin'),
            self::USER => new LookupTableBasicValue(self::USER, 'User'),
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
        return empty($all[$id]) ? 'Unknown' : $all[$id]->getName();
    }
}
