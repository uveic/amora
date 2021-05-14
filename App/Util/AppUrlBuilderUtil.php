<?php

namespace Amora\App\Util;

use Amora\Core\Util\UrlBuilderUtil;

final class AppUrlBuilderUtil extends UrlBuilderUtil
{
    const AUTHORISED_DASHBOARD = '/dashboard';

    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////
    // Authorised Html Controller

    public static function getAuthorisedDashboardUrl(string $languageIsoCode): string
    {
        return self::getBaseLinkUrl($languageIsoCode) . self::AUTHORISED_DASHBOARD;
    }
}
