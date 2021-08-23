<?php

namespace Amora\Core\Module\Article\Value;

use Amora\Core\Model\Util\LookupTableBasicValue;

final class ArticleType
{
    const HOMEPAGE = 1;
    const BLOG = 3;
    const PAGE = 4;

    public static function getAll(): array
    {
        return [
            self::HOMEPAGE => new LookupTableBasicValue(self::HOMEPAGE, 'Home'),
            self::BLOG => new LookupTableBasicValue(self::BLOG, 'Blog'),
            self::PAGE => new LookupTableBasicValue(self::PAGE, 'Page'),
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
