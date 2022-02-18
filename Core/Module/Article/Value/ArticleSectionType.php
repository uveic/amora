<?php

namespace Amora\Core\Module\Article\Value;

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
            self::TextParagraph,
            self::TextTitle,
            self::TextSubtitle,
            self::Image,
            self::YoutubeVideo,
        ];
    }
}
