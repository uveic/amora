<?php

namespace Amora\Core\Util;

use Amora\Core\Core;

final class UrlBuilderUtil
{
    const APP_DASHBOARD_URL_PATH = '/dashboard';
    const BACKOFFICE_DASHBOARD_URL_PATH = '/backoffice/dashboard';
    const BACKOFFICE_USERS = '/backoffice/users';

    const PUBLIC_API_PASSWORD_RESET = '/papi/login/password-reset';
    const PUBLIC_API_PASSWORD_CREATION = '/papi/login/password-creation';

    public static function getBaseLinkUrl(string $siteLanguage): string
    {
        $baseUrl = Core::getConfigValue('baseUrl');
        $siteLanguage = strtolower($siteLanguage);

        return trim($baseUrl, ' /') . '/' . $siteLanguage;
    }

    public static function getCreatePasswordUrl(
        string $languageIsoCode,
        string $verificationIdentifier
    ): string {
        return self::getBaseLinkUrl($languageIsoCode) . '/user/create/' . $verificationIdentifier;
    }

    public static function getEmailUpdateUrl(
        string $languageIsoCode,
        string $verificationIdentifier
    ): string {
        return self::getBaseLinkUrl($languageIsoCode) . '/user/verify/' . $verificationIdentifier;
    }

    public static function getVerificationEmailUrl(
        string $languageIsoCode,
        string $verificationIdentifier
    ): string {
        return self::getBaseLinkUrl($languageIsoCode) . '/user/verify/' . $verificationIdentifier;
    }

    public static function getPasswordResetUrl(
        string $languageIsoCode,
        string $verificationIdentifier
    ): string
    {
        return self::getBaseLinkUrl($languageIsoCode) . '/user/reset/' . $verificationIdentifier;
    }

    public static function getAdminDashboardUrl(string $languageIsoCode): string
    {
        return self::getBaseLinkUrl($languageIsoCode) . self::BACKOFFICE_DASHBOARD_URL_PATH;
    }

    public static function getUserDashboardUrl(string $languageIsoCode): string
    {
        return self::getBaseLinkUrl($languageIsoCode) . self::APP_DASHBOARD_URL_PATH;
    }
}
