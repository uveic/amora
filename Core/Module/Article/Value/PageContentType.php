<?php

namespace Amora\Core\Module\Article\Value;

use Amora\App\Value\AppPageContentType;
use Amora\App\Value\Language;
use Amora\Core\Util\UrlBuilderUtil;

enum PageContentSection {
    case Title;
    case Subtitle;
    case Content;
    case MainImage;
    case ActionUrl;
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

    public static function buildRedirectUrl(self|AppPageContentType|null $type, Language $language): string
    {
        return match ($type) {
            self::BlogBottom => UrlBuilderUtil::buildBackofficeDashboardUrl($language),
            self::Homepage => UrlBuilderUtil::buildBaseUrl($language),
            default => UrlBuilderUtil::buildBackofficeContentListUrl($language),
        };
    }
}
