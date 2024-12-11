<?php

namespace Amora\Core\Value;

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Util\MenuItem;
use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\UrlBuilderUtil;

final class CoreMenu
{
    public static function getAdmin(
        Language $language,
        ?string $username = null,
        bool $includeAuthorisedDashboardLink = false,
    ): array {
        $localisationUtil = Core::getLocalisationUtil($language);

        $output = [
            new MenuItem(
                path: UrlBuilderUtil::buildBackofficeDashboardUrl($language),
                text: $localisationUtil->getValue('navAdministrator'),
                sequence: 100,
            ),
            new MenuItem(
                text: $localisationUtil->getValue('navAdminContent'),
                icon: CoreIcons::CARET_DOWN,
                children: [
                    new MenuItem(
                        path: UrlBuilderUtil::buildBackofficeContentListUrl($language),
                        text: $localisationUtil->getValue('navAdminPageContentEdit'),
                        icon: CoreIcons::ARTICLE,
                        sequence: 2000,
                    ),
                    new MenuItem(
                        path: UrlBuilderUtil::buildBackofficeAlbumListUrl($language),
                        text: $localisationUtil->getValue('navAdminAlbums'),
                        icon: CoreIcons::IMAGES,
                        sequence: 2010,
                    ),
                    new MenuItem(
                        path: UrlBuilderUtil::buildBackofficeImageListUrl($language),
                        text: $localisationUtil->getValue('navAdminImages'),
                        icon: CoreIcons::IMAGE,
                        sequence: 2020,
                    ),
                    new MenuItem(
                        path: UrlBuilderUtil::buildBackofficeMediaListUrl($language),
                        text: $localisationUtil->getValue('navAdminMedia'),
                        icon: CoreIcons::FILES,
                        sequence: 2030,
                    ),
                    new MenuItem(
                        path: UrlBuilderUtil::buildBackofficeArticleListUrl($language, ArticleType::Page),
                        text: $localisationUtil->getValue('navAdminArticles'),
                        icon: CoreIcons::ARTICLE,
                        sequence: 2040,
                    ),
                    new MenuItem(
                        path: UrlBuilderUtil::buildBackofficeArticleListUrl($language, ArticleType::Blog),
                        text: $localisationUtil->getValue('navAdminBlogPosts'),
                        icon: CoreIcons::ARTICLE,
                        sequence: 2050,
                    ),
                    new MenuItem(
                        path: UrlBuilderUtil::buildBackofficeUserListUrl($language),
                        text: $localisationUtil->getValue('navAdminUsers'),
                        icon: CoreIcons::USERS,
                        sequence: 2060,
                    ),
                    new MenuItem(
                        path: UrlBuilderUtil::buildBackofficeAnalyticsUrl($language, eventType: EventType::Visitor),
                        text: $localisationUtil->getValue('navAdminAnalytics'),
                        icon: CoreIcons::CHART_LINE,
                        sequence: 2070,
                    ),
                    new MenuItem(
                        path: UrlBuilderUtil::buildBackofficeMailsUrl($language),
                        text: $localisationUtil->getValue('navAdminEmails'),
                        icon: CoreIcons::ENVELOPE_SIMPLE,
                        sequence: 2080,
                    ),
                ],
                sequence: 200,
            ),
            new MenuItem(
                text: $username ?? $localisationUtil->getValue('navAccount'),
                icon: CoreIcons::CARET_DOWN,
                children: [
                    new MenuItem(
                        path: UrlBuilderUtil::buildAuthorisedAccountUrl($language),
                        text: $localisationUtil->getValue('navAccount'),
                        icon: CoreIcons::USER,
                    ),
                    new MenuItem(
                        path: UrlBuilderUtil::buildAuthorisedLogoutUrl($language),
                        text: $localisationUtil->getValue('navSignOut'),
                        icon: CoreIcons::SIGN_OUT,
                    ),
                ],
                sequence: 9999,
            ),
        ];

        if ($includeAuthorisedDashboardLink) {
            $output[] = new MenuItem(
                path: UrlBuilderUtil::buildAppDashboardUrl($language),
                text: $localisationUtil->getValue('navAuthorisedDashboard'),
                sequence: 0,
            );
        }

        return $output;
    }

    public static function getCustomer(
        Language $language,
        ?string $username = null,
        bool $includeAdminLink = false,
    ): array {
        $localisationUtil = Core::getLocalisationUtil($language);

        $output = [];
        if ($includeAdminLink) {
            $output[] = new MenuItem(
                path: UrlBuilderUtil::buildBackofficeDashboardUrl($language),
                text: $localisationUtil->getValue('navAdministrator'),
                sequence: 0,
            );
        }

        $output[] = new MenuItem(
            path: UrlBuilderUtil::buildAppDashboardUrl($language),
            text: $localisationUtil->getValue('navAuthorisedDashboard')
        );
        $output[] = new MenuItem(
            text: $username ?? $localisationUtil->getValue('navAccount'),
            icon: CoreIcons::CARET_DOWN,
            children: [
                new MenuItem(
                    path: UrlBuilderUtil::buildAuthorisedAccountUrl($language),
                    text: $localisationUtil->getValue('navAccount'),
                    icon: CoreIcons::USER,
                ),
                new MenuItem(
                    path: UrlBuilderUtil::buildAuthorisedLogoutUrl($language),
                    text: $localisationUtil->getValue('navSignOut'),
                    icon: CoreIcons::SIGN_OUT,
                ),
            ],
            sequence: 9999,
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
