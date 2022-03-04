<?php

namespace Amora\App\Value;

use Amora\Core\Core;
use Amora\Core\Menu\MenuItem;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreMenu;

final class AppMenu
{
    public static function getAdminAll(
        Language $language,
        ?string $username = null,
    ): array {
        $appMenu = [];

        $output = array_merge(
            CoreMenu::getAdminMenu($language, $username),
            $appMenu
        );

        usort($output, function($a, $b) {
            return $a->order - $b->order;
        });

        return $output;
    }

    public static function getCustomerAll(
        Language $language,
        string $username = null,
        bool $includeAdminLink = false,
    ): array {
        $localisationUtil = Core::getLocalisationUtil($language, false);

        $appMenu = [];

        $output = array_merge(
            CoreMenu::getUserMenu($language, $username),
            $appMenu
        );

        if ($includeAdminLink) {
            $output[] = new MenuItem(
                uri: UrlBuilderUtil::buildBackofficeDashboardUrl($language),
                text: $localisationUtil->getValue('navAdministrator'),
                order: 1
            );
        }

        usort($output, function($a, $b) {
            return $a->order - $b->order;
        });

        return $output;
    }
}
