<?php

namespace Amora\Core\Util;

use Amora\App\Value\AppPageContentType;
use Amora\Core\Core;
use Amora\App\Value\Language;
use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Module\Analytics\Value\Period;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\Article\Value\PageContentType;

class UrlBuilderUtil
{
    // Backoffice URLs
    const string BACKOFFICE_DASHBOARD_URL_PATH = '/backoffice/dashboard';

    const string BACKOFFICE_USER_LIST = '/backoffice/users';
    const string BACKOFFICE_USER_VIEW = '/backoffice/users/%d';
    const string BACKOFFICE_USER_EDIT = '/backoffice/users/%d/edit';
    const string BACKOFFICE_USER_NEW = '/backoffice/users/new';

    const string BACKOFFICE_IMAGES = '/backoffice/images';
    const string BACKOFFICE_MEDIA = '/backoffice/media';
    const string BACKOFFICE_ANALYTICS = '/backoffice/analytics';

    const string BACKOFFICE_ARTICLES = '/backoffice/articles';
    const string BACKOFFICE_ARTICLE = '/backoffice/articles/%d';
    const string BACKOFFICE_ARTICLE_NEW = '/backoffice/articles/new';
    const string BACKOFFICE_ARTICLE_PREVIEW = '/backoffice/articles/%d/preview';

    const string BACKOFFICE_ALBUM_LIST = '/backoffice/albums';
    const string BACKOFFICE_ALBUM_VIEW = '/backoffice/albums/%d';
    const string BACKOFFICE_ALBUM_EDIT = '/backoffice/albums/%d/edit';
    const string BACKOFFICE_ALBUM_NEW = '/backoffice/albums/new';

    const string BACKOFFICE_CONTENT = '/backoffice/content';
    const string BACKOFFICE_CONTENT_TYPE_EDIT = '/backoffice/content-type/%d/language/%s';

    const string BACKOFFICE_EMAILS = '/backoffice/emails';

    // Authorised URLs
    const string AUTHORISED_ACCOUNT = '/account';
    const string AUTHORISED_ACCOUNT_PASSWORD = '/account/password';
    const string AUTHORISED_ACCOUNT_DOWNLOAD = '/account/download';
    const string AUTHORISED_ACCOUNT_DELETE = '/account/delete';
    const string AUTHORISED_LOGOUT = '/logout';
    const string APP_DASHBOARD_URL_PATH = '/dashboard';

    // Public URLs
    const string PUBLIC_API_PASSWORD_RESET = '/papi/login/password-reset';
    const string PUBLIC_API_PASSWORD_CREATION = '/papi/login/password-creation';

    const string PUBLIC_HTML_INVITE_REQUEST = '/invite-request';
    const string PUBLIC_HTML_LOGIN = '/login';
    const string PUBLIC_HTML_REGISTER = '/register';
    const string PUBLIC_HTML_LOGIN_FORGOT = '/login/forgot';
    const string PUBLIC_CREATE_PASSWORD = '/user/create/%s';
    const string PUBLIC_VERIFY_USER = '/user/verify/%s';
    const string PUBLIC_RESET_PASSWORD = '/user/reset/%s';

    const string PUBLIC_ALBUM_VIEW = '/album/%s';

    const string PUBLIC_RSS = '/rss';
    const string PUBLIC_JSON_FEED = '/json-feed';

    const string PUBLIC_API_LOGIN = '/papi/login';
    const string PUBLIC_API_LOGIN_FORGOT = '/papi/login/forgot';

    public static function buildBaseUrl(Language $siteLanguage, bool $includeHiddenToken = false): string
    {
        $config = Core::getConfig();
        $baseUrl = $config->baseUrl;

        return trim($baseUrl, ' /') .
            (count(Core::getEnabledSiteLanguages()) > 1
                ? '/' . strtolower($siteLanguage->value)
                : ''
            ) . ($includeHiddenToken && $config->hiddenSiteToken ? '?ht=' . $config->hiddenSiteToken : '');
    }

    public static function buildBaseUrlWithoutLanguage(bool $includeHiddenToken = false): string
    {
        $config = Core::getConfig();
        $baseUrl = $config->baseUrl;

        return trim($baseUrl, ' /') .
            ($includeHiddenToken && $config->hiddenSiteToken ? '?ht=' . $config->hiddenSiteToken : '');
    }

    public static function buildPublicHomeUrlWithHash(Language $language, string $hash): string
    {
        $config = Core::getConfig();

        return self::buildBaseUrl($language) .
            ($config->hiddenSiteToken ? '?ht=' . $config->hiddenSiteToken : '') .
            '#' . $hash;
    }

    public static function buildMediaBaseUrl(): string
    {
        $baseUrl = Core::getConfig()->baseUrl;
        $mediaUrl = Core::getConfig()->mediaBaseUrl;

        return trim($baseUrl, ' /') . '/' . trim($mediaUrl, '/ ');
    }

    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    /// Backoffice URLs

    public static function buildBackofficeDashboardUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::BACKOFFICE_DASHBOARD_URL_PATH;
    }

    public static function buildBackofficeImageListUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::BACKOFFICE_IMAGES;
    }

    public static function buildBackofficeMediaListUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::BACKOFFICE_MEDIA;
    }

    public static function buildBackofficeArticleListUrl(
        Language $language,
        ?ArticleType $type = null,
    ): string {
        return self::buildBaseUrl($language)
            . self::BACKOFFICE_ARTICLES
            . ($type ? '?atId=' . $type->value : '');
    }

    public static function buildBackofficeArticleUrl(Language $language, int $articleId): string
    {
        return self::buildBaseUrl($language) . sprintf(self::BACKOFFICE_ARTICLE, $articleId);
    }

    public static function buildBackofficeArticleNewUrl(
        Language $language,
        ArticleType $articleType,
    ): string {
        return self::buildBaseUrl($language)
            . self::BACKOFFICE_ARTICLE_NEW
            . '?atId=' . $articleType->value;
    }

    public static function buildBackofficeArticlePreviewUrl(Language $language, int $articleId): string
    {
        return self::buildBaseUrl($language) . sprintf(self::BACKOFFICE_ARTICLE_PREVIEW, $articleId);
    }

    public static function buildBackofficeContentListUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::BACKOFFICE_CONTENT;
    }

    public static function buildBackofficeContentEditUrl(
        Language $language,
        PageContentType|AppPageContentType $contentType,
        Language $contentTypeLanguage,
    ): string {
        return self::buildBaseUrl($language)
            . sprintf(self::BACKOFFICE_CONTENT_TYPE_EDIT, $contentType->value, $contentTypeLanguage->value);
    }

    public static function buildBackofficeAnalyticsUrl(
        Language $language,
        ?Period $period = null,
        ?string $date = null,
        ?EventType $eventType = null,
        ?int $itemsCount = null,
    ): string {
        $getParams = [];

        $baseUrl = self::buildBaseUrl($language) . self::BACKOFFICE_ANALYTICS;

        if ($period) {
            $getParams[] = 'period=' . $period->getName();
        }

        if ($date) {
            $getParams[] = 'date=' . $date;
        }

        if ($eventType) {
            $getParams[] = 'eventTypeId=' . $eventType->value;
        }

        if ($itemsCount) {
            $getParams[] = 'itemsCount=' . $itemsCount;
        }

        return $baseUrl . ($getParams ? ('?' . implode('&', $getParams)) : '');
    }

    public static function buildBackofficeMailsUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::BACKOFFICE_EMAILS;
    }

    public static function buildBackofficeUserListUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::BACKOFFICE_USER_LIST;
    }

    public static function buildBackofficeUserViewUrl(Language $language, int $userId): string
    {
        return self::buildBaseUrl($language) . sprintf(self::BACKOFFICE_USER_VIEW, $userId);
    }

    public static function buildBackofficeUserEditUrl(Language $language, int $userId): string
    {
        return self::buildBaseUrl($language) . sprintf(self::BACKOFFICE_USER_EDIT, $userId);
    }

    public static function buildBackofficeUserNewUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::BACKOFFICE_USER_NEW;
    }

    public static function buildBackofficeAlbumListUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::BACKOFFICE_ALBUM_LIST;
    }

    public static function buildBackofficeAlbumViewUrl(Language $language, int $albumId): string
    {
        return self::buildBaseUrl($language) . sprintf(self::BACKOFFICE_ALBUM_VIEW, $albumId);
    }

    public static function buildBackofficeAlbumEditUrl(Language $language, int $albumId): string
    {
        return self::buildBaseUrl($language) . sprintf(self::BACKOFFICE_ALBUM_EDIT, $albumId);
    }

    public static function buildBackofficeAlbumNewUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::BACKOFFICE_ALBUM_NEW;
    }

    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    /// Authorised URLs

    public static function buildAppDashboardUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::APP_DASHBOARD_URL_PATH;
    }

    public static function buildAuthorisedAccountUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::AUTHORISED_ACCOUNT;
    }

    public static function buildAuthorisedAccountPasswordUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::AUTHORISED_ACCOUNT_PASSWORD;
    }

    public static function buildAuthorisedAccountDownloadUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::AUTHORISED_ACCOUNT_DOWNLOAD;
    }

    public static function buildAuthorisedAccountDeleteUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::AUTHORISED_ACCOUNT_DELETE;
    }

    public static function buildAuthorisedLogoutUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::AUTHORISED_LOGOUT;
    }

    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    /// Public URLs

    public static function buildPublicInviteRequestUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::PUBLIC_HTML_INVITE_REQUEST;
    }

    public static function buildPublicLoginUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::PUBLIC_HTML_LOGIN;
    }

    public static function buildPublicRegisterUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::PUBLIC_HTML_REGISTER;
    }

    public static function buildPublicLoginForgotUrl(Language $language): string
    {
        return self::buildBaseUrl($language) . self::PUBLIC_HTML_LOGIN_FORGOT;
    }

    public static function buildPublicHomepageUrl(Language $language): string
    {
        return self::buildBaseUrl($language);
    }

    public static function buildPublicCreatePasswordUrl(
        Language $language,
        string $verificationIdentifier
    ): string {
        return self::buildBaseUrl($language) .
            sprintf(self::PUBLIC_CREATE_PASSWORD, $verificationIdentifier);
    }

    public static function buildPublicEmailUpdateUrl(
        Language $language,
        string $verificationIdentifier
    ): string {
        return self::buildBaseUrl($language) .
            sprintf(self::PUBLIC_VERIFY_USER, $verificationIdentifier);
    }

    public static function buildPublicVerificationEmailUrl(
        Language $language,
        string $verificationIdentifier
    ): string {
        return self::buildBaseUrl($language) .
            sprintf(self::PUBLIC_VERIFY_USER, $verificationIdentifier);
    }

    public static function buildPublicPasswordResetUrl(
        Language $language,
        string $verificationIdentifier
    ): string
    {
        return self::buildBaseUrl($language) .
            sprintf(self::PUBLIC_RESET_PASSWORD, $verificationIdentifier);
    }

    public static function buildPublicArticlePath(
        string $path,
        ?Language $language = null,
    ): string {
        return (empty($language) || count(Core::getEnabledSiteLanguages()) === 1
                ? self::buildBaseUrlWithoutLanguage()
                : self::buildBaseUrl($language)
            )
            . '/' . $path;
    }

    public static function buildPublicAlbumUrl(
        string $slug,
        ?Language $language = null,
    ): string {
        return (empty($language) || count(Core::getEnabledSiteLanguages()) === 1
                ? self::buildBaseUrlWithoutLanguage()
                : self::buildBaseUrl($language)
            )
            . sprintf(self::PUBLIC_ALBUM_VIEW, $slug);
    }

    public static function buildPublicRssUrl(): string
    {
        return self::buildBaseUrlWithoutLanguage() . self::PUBLIC_RSS;
    }

    public static function buildPublicJsonFeedUrl(): string
    {
        return self::buildBaseUrlWithoutLanguage() . self::PUBLIC_JSON_FEED;
    }
}
