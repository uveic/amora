<?php

namespace Amora\Core\Module\Article\Value;

use Amora\App\Value\Language;
use Amora\Core\Util\UrlBuilderUtil;

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

    public static function buildRedirectUrl(self $type, Language $language): string
    {
        if ($type === self::BlogBottom) {
            return UrlBuilderUtil::buildBackofficeDashboardUrl($language);
        }

        return UrlBuilderUtil::buildBaseUrl($language);
    }
}
