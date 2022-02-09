<?php

namespace Amora\Core\Module\Article\Value;

use Amora\Core\Model\Util\LookupTableBasicValue;

enum ArticleSectionType: int
{
    case TextParagraph = 1;
    case Image = 2;
    case YoutubeVideo = 3;
    case TextTitle = 4;
    case TextSubtitle = 5;

    public static function getAll(): array
    {
        return [
            self::TextParagraph->value => new LookupTableBasicValue(self::TextParagraph->value, self::TextParagraph->name),
            self::TextTitle->value => new LookupTableBasicValue(self::TextTitle->value, self::TextTitle->name),
            self::TextSubtitle->value => new LookupTableBasicValue(self::TextSubtitle->value, self::TextSubtitle->name),
            self::Image->value => new LookupTableBasicValue(self::Image->value, self::Image->name),
            self::YoutubeVideo->value => new LookupTableBasicValue(self::YoutubeVideo->value, self::YoutubeVideo->name),
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
