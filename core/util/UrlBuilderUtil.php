<?php

namespace uve\core\util;

use uve\core\Core;

final class UrlBuilderUtil
{
    const APP_DASHBOARD_URL_PATH = '/dashboard';
    const BACKOFFICE_DASHBOARD_URL_PATH = '/backoffice/dashboard';

    public static function getBaseLinkUrl(string $siteLanguage): string
    {
        $baseUrl = Core::getConfigValue('baseUrl');
        $siteLanguage = strtolower($siteLanguage);

        return trim($baseUrl, ' /') . '/' . $siteLanguage;
    }
}
