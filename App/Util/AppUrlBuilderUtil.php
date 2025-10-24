<?php

namespace Amora\App\Util;

use Amora\App\Value\Language;
use Amora\Core\Util\UrlBuilderUtil;

final class AppUrlBuilderUtil extends UrlBuilderUtil
{
    private const string AUTHORISED_DASHBOARD = '/dashboard';

    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////
    // Authorised Html Controller

    public static function getAuthorisedDashboardUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::AUTHORISED_DASHBOARD;
    }
}
