<?php

namespace Amora\Core\Module\Article\Value;

use Amora\Core\Model\Util\LookupTableBasicValue;

enum ArticleStatus: int
{
    case PUBLISHED = 1;
    case DELETED = 2;
    case DRAFT = 3;
    case PRIVATE = 4;

    public static function getAll(): array
    {
        return [
            self::PUBLISHED->value => new LookupTableBasicValue(self::PUBLISHED->value, 'Published'),
            self::DELETED->value =>  new LookupTableBasicValue(self::DELETED->value, 'Deleted'),
            self::DRAFT->value =>  new LookupTableBasicValue(self::DRAFT->value, 'Draft'),
            self::PRIVATE->value =>  new LookupTableBasicValue(self::PRIVATE->value, 'Private'),
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

    public static function getStatusForId(int $id): ?LookupTableBasicValue
    {
        $all = self::getAll();
        return empty($all[$id]) ? null : $all[$id];
    }
}
