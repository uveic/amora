<?php

namespace uve\App\Value;

use uve\core\Core;

final class AppMenu
{
    public static function getAll(string $baseUrlWithLanguage, string $languageIsoCode): array
    {
        $localisationUtil = Core::getLocalisationUtil($languageIsoCode);
        return [];
    }
}
