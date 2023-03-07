<?php

namespace Amora\App\Value;

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
}
