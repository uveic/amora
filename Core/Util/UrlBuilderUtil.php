<?php

namespace Amora\Core\Util;

use Amora\Core\Core;

class UrlBuilderUtil
{
    // Backoffice URLs
    const BACKOFFICE_DASHBOARD_URL_PATH = '/backoffice/dashboard';
    const BACKOFFICE_USERS = '/backoffice/users';
    const BACKOFFICE_IMAGES = '/backoffice/images';
    const BACKOFFICE_ARTICLES = '/backoffice/articles';

    // Authorised URLs
    const AUTHORISED_ACCOUNT = '/account';
    const AUTHORISED_LOGOUT = '/logout';
    const APP_DASHBOARD_URL_PATH = '/dashboard';

    // Public URLs
    const PUBLIC_API_PASSWORD_RESET = '/papi/login/password-reset';
    const PUBLIC_API_PASSWORD_CREATION = '/papi/login/password-creation';

    const PUBLIC_HTML_INVITE_REQUEST = '/invite-request';
    const PUBLIC_HTML_LOGIN = '/login';
    const PUBLIC_CREATE_PASSWORD = '/user/create/%s';
    const PUBLIC_VERIFY_USER = '/user/verify/%s';
    const PUBLIC_RESET_PASSWORD = '/user/reset/%s';

    protected static function getBaseLinkUrl(string $siteLanguage): string
    {
        $baseUrl = Core::getConfigValue('baseUrl');
        $siteLanguage = strtolower($siteLanguage);

        return trim($baseUrl, ' /') . '/' . $siteLanguage;
    }

    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    /// Backoffice URLs

    public static function getBackofficeDashboardUrl(string $languageIsoCode): string
    {
        return self::getBaseLinkUrl($languageIsoCode) . self::BACKOFFICE_DASHBOARD_URL_PATH;
    }

    public static function getBackofficeImagesUrl(string $languageIsoCode): string
    {
        return self::getBaseLinkUrl($languageIsoCode) . self::BACKOFFICE_IMAGES;
    }

    public static function getBackofficeArticlesUrl(string $languageIsoCode): string
    {
        return self::getBaseLinkUrl($languageIsoCode) . self::BACKOFFICE_ARTICLES;
    }

    public static function getBackofficeUsersUrl(string $languageIsoCode): string
    {
        return self::getBaseLinkUrl($languageIsoCode) . self::BACKOFFICE_USERS;
    }

    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    /// Authorised URLs

    public static function getAppDashboardUrl(string $languageIsoCode): string
    {
        return self::getBaseLinkUrl($languageIsoCode) . self::APP_DASHBOARD_URL_PATH;
    }

    public static function getAuthorisedAccountUrl(string $languageIsoCode): string
    {
        return self::getBaseLinkUrl($languageIsoCode) . self::AUTHORISED_ACCOUNT;
    }

    public static function getAuthorisedLogoutUrl(string $languageIsoCode): string
    {
        return self::getBaseLinkUrl($languageIsoCode) . self::AUTHORISED_LOGOUT;
    }

    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    /// Public URLs

    public static function getPublicInviteRequestUrl(string $languageIsoCode): string
    {
        return self::getBaseLinkUrl($languageIsoCode) . self::PUBLIC_HTML_INVITE_REQUEST;
    }

    public static function getPublicLoginUrl(string $languageIsoCode): string
    {
        return self::getBaseLinkUrl($languageIsoCode) . self::PUBLIC_HTML_LOGIN;
    }

    public static function getPublicHomepageUrl(string $languageIsoCode): string
    {
        return self::getBaseLinkUrl($languageIsoCode);
    }

    public static function getPublicCreatePasswordUrl(
        string $languageIsoCode,
        string $verificationIdentifier
    ): string {
        return self::getBaseLinkUrl($languageIsoCode) .
            sprintf(self::PUBLIC_CREATE_PASSWORD, $verificationIdentifier);
    }

    public static function getPublicEmailUpdateUrl(
        string $languageIsoCode,
        string $verificationIdentifier
    ): string {
        return self::getBaseLinkUrl($languageIsoCode) .
            sprintf(self::PUBLIC_VERIFY_USER, $verificationIdentifier);
    }

    public static function getPublicVerificationEmailUrl(
        string $languageIsoCode,
        string $verificationIdentifier
    ): string {
        return self::getBaseLinkUrl($languageIsoCode) .
            sprintf(self::PUBLIC_VERIFY_USER, $verificationIdentifier);
    }

    public static function getPublicPasswordResetUrl(
        string $languageIsoCode,
        string $verificationIdentifier
    ): string
    {
        return self::getBaseLinkUrl($languageIsoCode) .
            sprintf(self::PUBLIC_RESET_PASSWORD, $verificationIdentifier);
    }
}
