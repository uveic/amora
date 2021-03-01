<?php

namespace Amora\Core\Value;

use Amora\Core\Core;
use Amora\Core\Menu\MenuItem;

final class CoreMenu
{
    public static function getAll(string $baseUrlWithLanguage, string $languageIsoCode): array
    {
        $localisationUtil = Core::getLocalisationUtil($languageIsoCode);
        return [
            new MenuItem(
                $baseUrlWithLanguage . 'backoffice/dashboard',
                $localisationUtil->getValue('navAdminDashboard'),
                null,
                null,
                true
            ),
            new MenuItem(
                $baseUrlWithLanguage . 'backoffice/images',
                $localisationUtil->getValue('navAdminImages'),
                null,
                null,
                true
            ),
            new MenuItem(
                $baseUrlWithLanguage . 'backoffice/articles',
                $localisationUtil->getValue('navAdminArticles'),
                null,
                null,
                true
            ),
            new MenuItem(
                $baseUrlWithLanguage . 'backoffice/users',
                $localisationUtil->getValue('navAdminUsers'),
                null,
                null,
                true
            )
        ];
    }

    public static function getAdminMenu(string $baseUrlWithLanguage, string $languageIsoCode): array
    {
        $output = [];
        $all = self::getAll($baseUrlWithLanguage, $languageIsoCode);
        foreach ($all as $item) {
            if ($item->isForAdmin()) {
                $output[] = $item;
            }
        }

        return $output;
    }
}
