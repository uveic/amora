<?php

namespace uve\core\module\article\value;

final class ArticleType
{
    const HOME = 1;
    const ARCHIVED = 2;

    public static function getAll(): array
    {
        return [
            self::HOME => [
                'id' => self::HOME,
                'name' => 'Home'
            ],
            self::ARCHIVED => [
                'id' => self::ARCHIVED,
                'name' => 'Archived'
            ]
        ];
    }

    public static function getNameForId(int $id): string
    {
        $all = self::getAll();
        return empty($all[$id]) ? 'Unknown' : $all[$id]['name'];
    }
}
