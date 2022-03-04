<?php

namespace Amora\Core\Value;

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Menu\MenuItem;
use Amora\Core\Util\UrlBuilderUtil;

final class CoreMenu
{
    public static function getAdminMenu(
        Language $language,
        string $username = null
    ): array {
        $localisationUtil = Core::getLocalisationUtil($language);

        return [
            new MenuItem(
                uri: UrlBuilderUtil::buildBackofficeDashboardUrl($language),
                text: $localisationUtil->getValue('navAdminDashboard'),
            ),
            new MenuItem(
                text: $localisationUtil->getValue('navAdminContent'),
                icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 256 256"><rect width="20" height="20" fill="none"></rect><polyline points="208 96 128 176 48 96" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polyline></svg>',
                children: [
                    new MenuItem(
                        uri: UrlBuilderUtil::buildBackofficeImagesUrl($language),
                        text: $localisationUtil->getValue('navAdminImages'),
                        icon: '<img class="img-svg m-r-05" width="20" height="20" src="/img/svg/image.svg" alt="' . $localisationUtil->getValue('navAdminImages') . '">',
                    ),
                    new MenuItem(
                        uri: UrlBuilderUtil::buildBackofficeArticlesUrl($language),
                        text: $localisationUtil->getValue('navAdminArticles'),
                        icon: '<img class="img-svg m-r-05" width="20" height="20" src="/img/svg/note-pencil-white.svg" alt="' . $localisationUtil->getValue('navAdminArticles') . '">',
                    ),
                    new MenuItem(
                        uri: UrlBuilderUtil::buildBackofficeBlogPostsUrl($language),
                        text: $localisationUtil->getValue('navAdminBlogPosts'),
                        icon: '<img class="img-svg m-r-05" width="20" height="20" src="/img/svg/article-medium-white.svg" alt="' . $localisationUtil->getValue('navAdminBlogPosts') . '">',
                    ),
                    new MenuItem(
                        uri: UrlBuilderUtil::buildBackofficeUsersUrl($language),
                        text: $localisationUtil->getValue('navAdminUsers'),
                        icon: '<img class="img-svg m-r-05" width="20" height="20" src="/img/svg/users-white.svg" alt="' . $localisationUtil->getValue('navAdminUsers') . '">',
                    ),
                ]
            ),
            new MenuItem(
                text: $username ?? $localisationUtil->getValue('navAccount'),
                icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 256 256"><rect width="20" height="20" fill="none"></rect><polyline points="208 96 128 176 48 96" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polyline></svg>',
                children: [
                new MenuItem(
                    uri: UrlBuilderUtil::buildAuthorisedAccountUrl($language),
                    text: $localisationUtil->getValue('navAccount'),
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" width="192" height="192" fill="#ffffff" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><circle cx="128" cy="96" r="64" fill="none" stroke="#ffffff" stroke-miterlimit="10" stroke-width="16"></circle><path d="M30.989,215.99064a112.03731,112.03731,0,0,1,194.02311.002" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path></svg>',
                ),
                new MenuItem(
                    uri: UrlBuilderUtil::buildAuthorisedLogoutUrl($language),
                    text: $localisationUtil->getValue('navSignOut'),
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" width="192" height="192" fill="#ffffff" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="174.029 86 216.029 128 174.029 170" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polyline><line x1="104" y1="128" x2="216" y2="128" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line><path d="M120,216H48a8,8,0,0,1-8-8V48a8,8,0,0,1,8-8h72" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path></svg>',
                ),
            ],
                order: 999
            ),
        ];
    }

    public static function getUserMenu(
        Language $language,
        string $username = null
    ): array {
        $localisationUtil = Core::getLocalisationUtil($language);

        return [
            new MenuItem(
                uri: UrlBuilderUtil::buildAppDashboardUrl($language),
                text: $localisationUtil->getValue('navDashboard'),
            ),
            new MenuItem(
                text: $username ?? $localisationUtil->getValue('navAccount'),
                icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 256 256"><rect width="20" height="20" fill="none"></rect><polyline points="208 96 128 176 48 96" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polyline></svg>',
                children: [
                new MenuItem(
                    uri: UrlBuilderUtil::buildAuthorisedAccountUrl($language),
                    text: $localisationUtil->getValue('navAccount'),
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" width="192" height="192" fill="#ffffff" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><circle cx="128" cy="96" r="64" fill="none" stroke="#ffffff" stroke-miterlimit="10" stroke-width="16"></circle><path d="M30.989,215.99064a112.03731,112.03731,0,0,1,194.02311.002" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path></svg>',
                ),
                new MenuItem(
                    uri: UrlBuilderUtil::buildAuthorisedLogoutUrl($language),
                    text: $localisationUtil->getValue('navSignOut'),
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" width="192" height="192" fill="#ffffff" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="174.029 86 216.029 128 174.029 170" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polyline><line x1="104" y1="128" x2="216" y2="128" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line><path d="M120,216H48a8,8,0,0,1-8-8V48a8,8,0,0,1,8-8h72" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path></svg>',
                ),
            ],
                order: 999
            ),
        ];
    }
}
