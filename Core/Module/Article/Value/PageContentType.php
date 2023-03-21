<?php

namespace Amora\Core\Module\Article\Value;

enum PageContentSection {
    case Title;
    case Subtitle;
    case Content;
    case MainImage;
}

enum PageContentType: int
{
    case Homepage = 1;
    case BlogBottom = 2;

    public static function getAll(): array
    {
        return [
            self::Homepage,
            self::BlogBottom,
        ];
    }
}
