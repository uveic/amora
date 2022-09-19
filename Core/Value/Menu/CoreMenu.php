<?php

namespace Amora\Core\Value;

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Util\MenuItem;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\UrlBuilderUtil;

final class CoreMenu
{
    public static function getAdmin(
        Language $language,
        string $username = null,
        bool $includeUserDashboardLink = false,
    ): array {
        $localisationUtil = Core::getLocalisationUtil($language);

        $output = [
            new MenuItem(
                path: UrlBuilderUtil::buildBackofficeDashboardUrl($language),
                text: $localisationUtil->getValue('navAdministrator'),
                order: 100,
            ),
            new MenuItem(
                text: $localisationUtil->getValue('navAdminContent'),
                icon: '<img class="img-svg" width="20" height="20" src="/img/svg/caret-down-white.svg" alt="Menu">',
                children: [
                    new MenuItem(
                        path: UrlBuilderUtil::buildBackofficeImagesUrl($language),
                        text: $localisationUtil->getValue('navAdminImages'),
                        icon: '<img class="img-svg m-r-05" width="20" height="20" src="/img/svg/image.svg" alt="' . $localisationUtil->getValue('navAdminImages') . '">',
                        order: 1001,
                    ),
                    new MenuItem(
                        path: UrlBuilderUtil::buildBackofficeMediaUrl($language),
                        text: $localisationUtil->getValue('navAdminMedia'),
                        icon: '<img class="img-svg m-r-05" width="20" height="20" src="/img/svg/files-white.svg" alt="' . $localisationUtil->getValue('navAdminMedia') . '">',
                        order: 1002,
                    ),
                    new MenuItem(
                        path: UrlBuilderUtil::buildBackofficeArticlesUrl($language, ArticleType::Page),
                        text: $localisationUtil->getValue('navAdminArticles'),
                        icon: ArticleType::getIcon(item: ArticleType::Page, class: 'm-r-05', white: true, alt: $localisationUtil->getValue('navAdminArticles')),
                        order: 1003,
                    ),
                    new MenuItem(
                        path: UrlBuilderUtil::buildBackofficeArticlesUrl($language, ArticleType::Blog),
                        text: $localisationUtil->getValue('navAdminBlogPosts'),
                        icon: ArticleType::getIcon(item: ArticleType::Blog, class: 'm-r-05', white: true, alt: $localisationUtil->getValue('navAdminBlogPosts')),
                        order: 1004,
                    ),
                    new MenuItem(
                        path: UrlBuilderUtil::buildBackofficeUsersUrl($language),
                        text: $localisationUtil->getValue('navAdminUsers'),
                        icon: '<img class="img-svg m-r-05" width="20" height="20" src="/img/svg/users-white.svg" alt="' . $localisationUtil->getValue('navAdminUsers') . '">',
                        order: 1005,
                    ),
                ],
                order: 101,
            ),
            new MenuItem(
                text: $username ?? $localisationUtil->getValue('navAccount'),
                icon: '<img class="img-svg m-l-05" width="20" height="20" src="/img/svg/caret-down-white.svg" alt="Menu">',
                children: [
                    new MenuItem(
                        path: UrlBuilderUtil::buildAuthorisedAccountUrl($language),
                        text: $localisationUtil->getValue('navAccount'),
                        icon: '<img class="img-svg m-r-05" width="20" height="20" src="/img/svg/user-white.svg" alt="' . $localisationUtil->getValue('navAccount') . '">',
                    ),
                    new MenuItem(
                        path: UrlBuilderUtil::buildAuthorisedLogoutUrl($language),
                        text: $localisationUtil->getValue('navSignOut'),
                        icon: '<img class="img-svg m-r-05" width="20" height="20" src="/img/svg/sign-out-white.svg" alt="' . $localisationUtil->getValue('navSignOut') . '">',
                    ),
                ],
                order: 9999,
            ),
        ];

        if ($includeUserDashboardLink) {
            $output[] = new MenuItem(
                path: UrlBuilderUtil::buildAppDashboardUrl($language),
                text: $localisationUtil->getValue('navDashboard'),
                order: 0,
            );
        }

        return $output;
    }

    public static function getCustomer(
        Language $language,
        string $username = null,
        bool $includeAdminLink = false,
        bool $whiteIcon = false,
    ): array {
        $localisationUtil = Core::getLocalisationUtil($language);

        $output = [];
        if ($includeAdminLink) {
            $output[] = new MenuItem(
                path: UrlBuilderUtil::buildBackofficeDashboardUrl($language),
                text: $localisationUtil->getValue('navAdministrator'),
                order: 0,
            );
        }

        $output[] = new MenuItem(
            path: UrlBuilderUtil::buildAppDashboardUrl($language),
            text: $localisationUtil->getValue('navDashboard')
        );
        $output[] = new MenuItem(
            text: $username ?? $localisationUtil->getValue('navAccount'),
            icon: '<img class="img-svg m-l-05" width="20" height="20" src="/img/svg/caret-down' . ($whiteIcon ? '-white' : '') . '.svg" alt="' . $localisationUtil->getValue('navAccount') . '">',
            children: [
                new MenuItem(
                    path: UrlBuilderUtil::buildAuthorisedAccountUrl($language),
                    text: $localisationUtil->getValue('navAccount'),
                    icon: '<img class="img-svg m-r-05" width="20" height="20" src="/img/svg/user' . ($whiteIcon ? '-white' : '') . '.svg" alt="' . $localisationUtil->getValue('navAccount') . '">',
                ),
                new MenuItem(
                    path: UrlBuilderUtil::buildAuthorisedLogoutUrl($language),
                    text: $localisationUtil->getValue('navSignOut'),
                    icon: '<img class="img-svg m-r-05" width="20" height="20" src="/img/svg/sign-out' . ($whiteIcon ? '-white' : '') . '.svg" alt="' . $localisationUtil->getValue('navSignOut') . '">',
                ),
            ],
            order: 9999,
        );

        return $output;
    }

    public static function getPublic(
        Language $language,
    ): array {
        $localisationUtil = Core::getLocalisationUtil($language);

        return [
            new MenuItem(
                path: UrlBuilderUtil::buildPublicLoginUrl($language),
                text: $localisationUtil->getValue('navSignIn'),
            ),
            new MenuItem(
                path: UrlBuilderUtil::buildPublicRegisterUrl($language),
                text: $localisationUtil->getValue('navSignUp'),
                class: 'action-register',
            ),
        ];
    }
}
