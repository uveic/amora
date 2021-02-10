<?php

namespace uve\core\module\article\value;

use uve\Core\Model\Util\LookupTableBasicValue;

final class ArticleType
{
    const HOME = 1;
    const ARCHIVED = 2;

    public static function getAll(): array
    {
        return [
            self::HOME => new LookupTableBasicValue(self::HOME, 'Home'),
            self::ARCHIVED => new LookupTableBasicValue(self::ARCHIVED, 'Archived')
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
