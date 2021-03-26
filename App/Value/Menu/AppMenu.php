<?php

namespace Amora\App\Value;

use Amora\Core\Core;
use Amora\Core\Value\CoreMenu;

final class AppMenu
{
    public static function getAdminAll(string $baseUrlWithLanguage, string $languageIsoCode): array
    {
        $localisationUtil = Core::getLocalisationUtil($languageIsoCode);
        $appMenu = [];

        return array_merge(
            CoreMenu::getAdminMenu($baseUrlWithLanguage, $languageIsoCode),
            $appMenu
        );
    }

    public static function getCustomerAll(
        string $baseUrlWithLanguage,
        string $languageIsoCode
    ): array {
        $localisationUtil = Core::getLocalisationUtil($languageIsoCode);
        return [];
    }
}
