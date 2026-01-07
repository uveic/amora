<?php

namespace Amora\App\Value;

use Amora\Core\Module\Article\Value\PageContentSection;
use Amora\Core\Module\Article\Value\PageContentType;

enum AppPageContentType: int
{
    public static function getAll(): array
    {
        return array_merge(
            PageContentType::getAll(),
            [],
        );
    }

    public static function getActive(): array
    {
        return [
            PageContentType::Homepage,
            PageContentType::BlogBottom,
        ];
    }

    public static function buildRedirectUrl(self|PageContentType|null $type, Language $language): string
    {
        return PageContentType::buildRedirectUrl($type, $language);
    }

    public static function displayContent(self|PageContentType $type, PageContentSection $section): bool
    {
        return match ($type) {
            PageContentType::BlogBottom => match ($section) {
                PageContentSection::Content => true,
                default => false,
            },
            PageContentType::Homepage => match ($section) {
                PageContentSection::Title, PageContentSection::Content => true,
                default => false,
            },
            default => true,
        };
    }

    public static function getTitleVariableName(self|PageContentType $type): string
    {
        return match ($type) {
            PageContentType::Homepage => 'pageContentEditTitleHomepage',
            PageContentType::BlogBottom => 'pageContentEditTitleBlogBottom',
            default => 'pageContentEditTitle' . $type->name,
        };
    }
}
