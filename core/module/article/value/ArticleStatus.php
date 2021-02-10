<?php

namespace uve\core\module\article\value;

use uve\Core\Model\Util\LookupTableBasicValue;

final class ArticleStatus
{
    const PUBLISHED = 1;
    const DELETED = 2;
    const DRAFT = 3;

    public static function getAll(): array
    {
        return [
            self::PUBLISHED => new LookupTableBasicValue(self::PUBLISHED, 'Published'),
            self::DELETED =>  new LookupTableBasicValue(self::DELETED, 'Deleted'),
            self::DRAFT =>  new LookupTableBasicValue(self::DRAFT, 'Draft')
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
