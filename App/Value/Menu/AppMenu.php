<?php

namespace Amora\App\Value;

use Amora\Core\Core;
use Amora\Core\Value\CoreMenu;
use Amora\Core\Menu\MenuItem;

final class AppMenu
{
    public static function getAdminAll(
        string $baseUrlWithLanguage,
        string $languageIsoCode,
        ?string $username = null,
    ): array {
        $appMenu = [];

        $output = array_merge(
            CoreMenu::getAdminMenu($baseUrlWithLanguage, $languageIsoCode, $username),
            $appMenu
        );

        usort($output, function($a, $b) {
            return $a->getOrder() - $b->getOrder();
        });

        return $output;
    }

    public static function getCustomerAll(
        string $baseUrlWithLanguage,
        string $languageIsoCode,
        string $username = null,
    ): array {
        $appMenu = [];

        $output = array_merge(
            CoreMenu::getUserMenu($baseUrlWithLanguage, $languageIsoCode, $username),
            $appMenu
        );

        usort($output, function($a, $b) {
            return $a->getOrder() - $b->getOrder();
        });

        return $output;
    }
}
