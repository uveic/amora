<?php

namespace Amora\App\Value;

use Amora\Core\Core;

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
