<?php

namespace Amora\Core\Util;

use Amora\Core\Core;

class UrlBuilderUtil
{
    // Backoffice URLs
    const BACKOFFICE_DASHBOARD_URL_PATH = '/backoffice/dashboard';

    const BACKOFFICE_USERS = '/backoffice/users';
    const BACKOFFICE_USER = '/backoffice/users/%d';
    const BACKOFFICE_NEW_USER = '/backoffice/users/new';

    const BACKOFFICE_IMAGES = '/backoffice/images';

    const BACKOFFICE_ARTICLES = '/backoffice/articles';
    const BACKOFFICE_ARTICLE = '/backoffice/articles/%d';
    const BACKOFFICE_NEW_ARTICLE = '/backoffice/articles/new';

    const BACKOFFICE_BLOG_POSTS = '/backoffice/blog-posts';
    const BACKOFFICE_BLOG_POST = '/backoffice/blog-posts/%d';
    const BACKOFFICE_NEW_BLOG_POST = '/backoffice/blog-posts/new';

    // Authorised URLs
    const AUTHORISED_ACCOUNT = '/account';
    const AUTHORISED_ACCOUNT_PASSWORD = '/account/password';
    const AUTHORISED_ACCOUNT_DOWNLOAD = '/account/download';
    const AUTHORISED_ACCOUNT_DELETE = '/account/delete';
    const AUTHORISED_LOGOUT = '/logout';
    const APP_DASHBOARD_URL_PATH = '/dashboard';

    // Public URLs
    const PUBLIC_API_PASSWORD_RESET = '/papi/login/password-reset';
    const PUBLIC_API_PASSWORD_CREATION = '/papi/login/password-creation';

    const PUBLIC_HTML_INVITE_REQUEST = '/invite-request';
    const PUBLIC_HTML_LOGIN = '/login';
    const PUBLIC_HTML_LOGIN_FORGOT = '/login/forgot';
    const PUBLIC_CREATE_PASSWORD = '/user/create/%s';
    const PUBLIC_VERIFY_USER = '/user/verify/%s';
    const PUBLIC_RESET_PASSWORD = '/user/reset/%s';

    const PUBLIC_RSS = '/rss';

    public static function buildBaseUrl(string $siteLanguage): string
    {
        $baseUrl = Core::getConfig()->baseUrl;
        $siteLanguage = strtolower($siteLanguage);

        return trim($baseUrl, ' /') . '/' . $siteLanguage;
    }

    public static function buildBaseUrlWithoutLanguage(): string
    {
        $baseUrl = Core::getConfig()->baseUrl;
        return trim($baseUrl, ' /');
    }

    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    /// Backoffice URLs

    public static function buildBackofficeDashboardUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::BACKOFFICE_DASHBOARD_URL_PATH;
    }

    public static function buildBackofficeImagesUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::BACKOFFICE_IMAGES;
    }

    public static function buildBackofficeArticlesUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::BACKOFFICE_ARTICLES;
    }

    public static function buildBackofficeArticleUrl(string $languageIsoCode, int $articleId): string
    {
        return self::buildBaseUrl($languageIsoCode) . sprintf(self::BACKOFFICE_ARTICLE, $articleId);
    }

    public static function buildBackofficeNewArticleUrl(
        string $languageIsoCode,
        ?int $articleTypeId = null
    ): string {
        return self::buildBaseUrl($languageIsoCode)
            . self::BACKOFFICE_NEW_ARTICLE
            . ($articleTypeId ? '?articleType=' . $articleTypeId : '');
    }

    public static function buildBackofficeUsersUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::BACKOFFICE_USERS;
    }

    public static function buildBackofficeUserUrl(string $languageIsoCode, int $userId): string
    {
        return self::buildBaseUrl($languageIsoCode) . sprintf(self::BACKOFFICE_USER, $userId);
    }

    public static function buildBackofficeNewUserUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::BACKOFFICE_NEW_USER;
    }

    public static function buildBackofficeBlogPostsUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::BACKOFFICE_BLOG_POSTS;
    }

    public static function buildBackofficeBlogPostUrl(string $languageIsoCode, int $articleId): string
    {
        return self::buildBaseUrl($languageIsoCode) . sprintf(self::BACKOFFICE_BLOG_POST, $articleId);
    }

    public static function buildBackofficeNewBlogPostUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::BACKOFFICE_NEW_BLOG_POST;
    }

    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    /// Authorised URLs

    public static function buildAppDashboardUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::APP_DASHBOARD_URL_PATH;
    }

    public static function buildAuthorisedAccountUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::AUTHORISED_ACCOUNT;
    }

    public static function buildAuthorisedAccountPasswordUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::AUTHORISED_ACCOUNT_PASSWORD;
    }

    public static function buildAuthorisedAccountDownloadUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::AUTHORISED_ACCOUNT_DOWNLOAD;
    }

    public static function buildAuthorisedAccountDeleteUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::AUTHORISED_ACCOUNT_DELETE;
    }

    public static function buildAuthorisedLogoutUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::AUTHORISED_LOGOUT;
    }

    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    /// Public URLs

    public static function buildPublicInviteRequestUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::PUBLIC_HTML_INVITE_REQUEST;
    }

    public static function buildPublicLoginUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::PUBLIC_HTML_LOGIN;
    }

    public static function buildPublicLoginForgotUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode) . self::PUBLIC_HTML_LOGIN_FORGOT;
    }

    public static function buildPublicHomepageUrl(string $languageIsoCode): string
    {
        return self::buildBaseUrl($languageIsoCode);
    }

    public static function buildPublicCreatePasswordUrl(
        string $languageIsoCode,
        string $verificationIdentifier
    ): string {
        return self::buildBaseUrl($languageIsoCode) .
            sprintf(self::PUBLIC_CREATE_PASSWORD, $verificationIdentifier);
    }

    public static function buildPublicEmailUpdateUrl(
        string $languageIsoCode,
        string $verificationIdentifier
    ): string {
        return self::buildBaseUrl($languageIsoCode) .
            sprintf(self::PUBLIC_VERIFY_USER, $verificationIdentifier);
    }

    public static function buildPublicVerificationEmailUrl(
        string $languageIsoCode,
        string $verificationIdentifier
    ): string {
        return self::buildBaseUrl($languageIsoCode) .
            sprintf(self::PUBLIC_VERIFY_USER, $verificationIdentifier);
    }

    public static function buildPublicPasswordResetUrl(
        string $languageIsoCode,
        string $verificationIdentifier
    ): string
    {
        return self::buildBaseUrl($languageIsoCode) .
            sprintf(self::PUBLIC_RESET_PASSWORD, $verificationIdentifier);
    }

    public static function buildPublicArticleUrl(
        string $uri,
        ?string $languageIsoCode = null,
    ): string {
        return ($languageIsoCode
                ? self::buildBaseUrl($languageIsoCode)
                : self::buildBaseUrlWithoutLanguage()
            )
            . '/' . $uri;
    }

    public static function buildPublicRssUrl(): string
    {
        return self::buildBaseUrlWithoutLanguage() . self::PUBLIC_RSS;
    }
}
