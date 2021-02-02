<?php

namespace uve\core\module\article\value;

final class ArticleSectionType
{
    const TEXT_PARAGRAPH = 1;
    const IMAGE = 2;
    const YOUTUBE_VIDEO = 3;
    const TEXT_TITLE = 4;
    const TEXT_SUBTITLE = 5;

    public static function getAll(): array
    {
        return [
            self::TEXT_PARAGRAPH => [
                'id' => self::TEXT_PARAGRAPH,
                'name' => 'Text - Paragraph'
            ],
            self::TEXT_TITLE => [
                'id' => self::TEXT_TITLE,
                'name' => 'Text - Title'
            ],
            self::TEXT_SUBTITLE => [
                'id' => self::TEXT_SUBTITLE,
                'name' => 'Text - Subtitle'
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
