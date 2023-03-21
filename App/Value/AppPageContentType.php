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

    public static function displayContent(self|PageContentType $type, PageContentSection $section): bool
    {
        return match ($type) {
            PageContentType::Homepage, PageContentType::BlogBottom => match ($section) {
                PageContentSection::Subtitle, PageContentSection::MainImage => false,
                default => true,
            }
        };
    }
}
