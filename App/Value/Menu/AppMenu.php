<?php

namespace Amora\App\Value;

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
    ): array {
        $appMenu = [];

        $output = array_merge(
            CoreMenu::getPublic(
                language: $language,
            ),
            $appMenu,
        );

        usort($output, function($a, $b) {
            return $a->sequence - $b->sequence;
        });

        return $output;
    }
}
