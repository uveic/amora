<?php

namespace Amora\App\Value;

use Amora\App\Util\AppUrlBuilderUtil;
use Amora\Core\Entity\Util\MenuItem;
use Amora\Core\Value\CoreMenu;

final class AppMenu
{
    public static function getAdmin(
        Language $language,
        ?string $username = null,
        bool $includeUserDashboardLink = true,
    ): array {
        $appMenu = [];

        $output = array_merge(
            CoreMenu::getAdmin(
                language: $language,
                username: $username,
                includeUserDashboardLink: $includeUserDashboardLink,
            ),
            $appMenu,
        );

        usort($output, function($a, $b) {
            return $a->sequence - $b->sequence;
        });

        return $output;
    }

    public static function getCustomer(
        Language $language,
        string $username = null,
        bool $includeAdminLink = false,
        bool $whiteIcon = false,
    ): array {
        $appMenu = [];

        $output = array_merge(
            CoreMenu::getCustomer(
                language: $language,
                username: $username,
                includeAdminLink: $includeAdminLink,
                whiteIcon: $whiteIcon,
            ),
            $appMenu,
        );

        usort($output, function($a, $b) {
            return $a->sequence - $b->sequence;
        });

        return $output;
    }

    public static function getPublic(
        Language $language,
        bool $isAdmin = false,
    ): array {
        $output = [];

        if ($isAdmin) {
            $output[] = new MenuItem(
                path: AppUrlBuilderUtil::buildBackofficeDashboardUrl($language),
                text: 'Admin',
            );
        }

        return array_merge(
            $output,
            CoreMenu::getPublic(
                language: $language,
            ),
        );
    }
}
