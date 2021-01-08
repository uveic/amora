<?php

namespace uve\core\module\article\value;

final class ArticleStatus
{
    const PUBLISHED = 1;
    const DELETED = 2;
    const DRAFT = 3;

    public static function getAll(): array
    {
        return [
            self::PUBLISHED => [
                'id' => self::PUBLISHED,
                'name' => 'Published'
            ],
            self::DELETED => [
                'id' => self::DELETED,
                'name' => 'Deleted'
            ],
            self::DRAFT => [
                'id' => self::DRAFT,
                'name' => 'Draft'
            ]
        ];
    }

    public static function getNameForId(int $id): string
    {
        $all = self::getAll();
        return empty($all[$id]) ? 'Unknown' : $all[$id]['name'];
    }
}
