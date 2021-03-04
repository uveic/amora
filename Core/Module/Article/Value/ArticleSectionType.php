<?php

namespace Amora\Core\Module\Article\Value;

use Amora\Core\Model\Util\LookupTableBasicValue;

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
            self::TEXT_PARAGRAPH => new LookupTableBasicValue(self::TEXT_PARAGRAPH, 'Text - Paragraph'),
            self::TEXT_TITLE => new LookupTableBasicValue(self::TEXT_TITLE, 'Text - Title'),
            self::TEXT_SUBTITLE => new LookupTableBasicValue(self::TEXT_SUBTITLE, 'Text - Subtitle'),
            self::IMAGE => new LookupTableBasicValue(self::IMAGE, 'Image'),
            self::YOUTUBE_VIDEO => new LookupTableBasicValue(self::YOUTUBE_VIDEO, 'YouTube Video'),
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
        return empty($all[$id]) ? 'Unknown' : $all[$id]['name'];
    }
}
