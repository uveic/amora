<?php

namespace Amora\App\Value;

use Amora\Core\Core;
use Amora\Core\Menu\MenuItem;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreMenu;

final class AppMenu
{
    public static function getAdminAll(
        string $languageIsoCode,
        ?string $username = null,
    ): array {
        $appMenu = [];

        $output = array_merge(
            CoreMenu::getAdminMenu($languageIsoCode, $username),
            $appMenu
        );

        usort($output, function($a, $b) {
            return $a->getOrder() - $b->getOrder();
        });

        return $output;
    }

    public static function getCustomerAll(
        string $languageIsoCode,
        string $username = null,
        bool $includeAdminLink = false,
    ): array {
        $localisationUtil = Core::getLocalisationUtil($languageIsoCode);

        $appMenu = [];

        $output = array_merge(
            CoreMenu::getUserMenu($languageIsoCode, $username),
            $appMenu
        );

        if ($includeAdminLink) {
            $output[] = new MenuItem(
                uri: UrlBuilderUtil::buildBackofficeDashboardUrl($languageIsoCode),
                text: $localisationUtil->getValue('navAdministrator'),
                order: 1
            );
        }

        usort($output, function($a, $b) {
            return $a->getOrder() - $b->getOrder();
        });

        return $output;
    }
}
