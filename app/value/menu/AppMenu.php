<?php

namespace uve\App\Value;

use uve\core\Core;

final class AppMenu
{
    public static function getAdminAll(string $baseUrlWithLanguage, string $languageIsoCode): array
    {
        $localisationUtil = Core::getLocalisationUtil($languageIsoCode);
        return [];
    }

    public static function getCustomerAll(
        string $baseUrlWithLanguage,
        string $languageIsoCode
    ): array {
        $localisationUtil = Core::getLocalisationUtil($languageIsoCode);
        return [];
    }
}
