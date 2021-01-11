<?php

namespace uve\core\module\article\value;

final class ArticleSectionType
{
    const TEXT = 1;
    const IMAGE = 2;
    const YOUTUBE_VIDEO = 3;

    public static function getAll(): array
    {
        return [
            self::TEXT => [
                'id' => self::TEXT,
                'name' => 'Text'
            ],
            self::IMAGE => [
                'id' => self::IMAGE,
                'name' => 'Image'
            ],
            self::YOUTUBE_VIDEO => [
                'id' => self::YOUTUBE_VIDEO,
                'name' => 'YouTube Video'
            ]
        ];
    }

    public static function getNameForId(int $id): string
    {
        $all = self::getAll();
        return empty($all[$id]) ? 'Unknown' : $all[$id]['name'];
    }
}
