<?php

namespace Amora\Core\Module\Article;

use Amora\App\Module\Article\App\ImageResizeApp;
use Amora\Core\Core;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Module\Album\AlbumCore;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Article\DataLayer\ArticleDataLayer;
use Amora\Core\Module\Article\DataLayer\TagDataLayer;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\DataLayer\MediaDataLayer;
use Amora\Core\Module\Article\Service\ImageService;
use Amora\Core\Module\Article\Service\MediaService;
use Amora\Core\Module\Article\Service\FeedService;
use Amora\Core\Module\Article\Service\TagService;

class ArticleCore extends Core
{
    public static function getDb(): MySqlDb
    {
        return self::getCoreDb();
    }

    public static function getArticleLogger(): Logger
    {
        return self::getLogger();
    }

    public static function getArticleDataLayer(): ArticleDataLayer
    {
        return self::getInstance(
            className: 'ArticleDataLayer',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Module/User/Model/User.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Article.php';
                require_once self::getPathRoot() . '/Core/Module/User/DataLayer/UserDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/Article/DataLayer/ArticleDataLayer.php';
                return new ArticleDataLayer(
                    db: self::getDb(),
                    logger: self::getArticleLogger(),
                    mediaDataLayer:  self::getMediaDataLayer(),
                    tagDataLayer:  self::getTagDataLayer(),
                );
            },
        );
    }

    public static function getArticleService(): ArticleService
    {
        return self::getInstance(
            className: 'ArticleService',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Util/Helper/ArticleHtmlGenerator.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/PageContentType.php';
                require_once self::getPathRoot() . '/App/Value/AppPageContentType.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/PageContent.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/ArticleStatus.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/ArticleSectionType.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/MediaType.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/MediaStatus.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Article.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/ArticlePath.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/ArticleSection.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Service/ArticleService.php';
                return new ArticleService(
                    logger:  self::getArticleLogger(),
                    articleDataLayer:  self::getArticleDataLayer(),
                    tagDataLayer:  self::getTagDataLayer(),
                );
            },
        );
    }

    public static function getMediaDataLayer(): MediaDataLayer
    {
        return self::getInstance(
            className: 'MediaDataLayer',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Module/Article/Value/ImageSize.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Media.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Article.php';
                require_once self::getPathRoot() . '/Core/Module/Article/DataLayer/MediaDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/Article/DataLayer/ArticleDataLayer.php';
                return new MediaDataLayer(
                    db: self::getDb(),
                    logger:  self::getArticleLogger(),
                );
            },
        );
    }

    public static function getMediaService(): MediaService
    {
        if (!isset(self::getConfig()->mediaBaseDir)) {
            self::getDefaultLogger()->logError("Missing 'mediaBaseDir' section from config");
            exit;
        }

        if (!isset(self::getConfig()->mediaBaseUrl)) {
            self::getDefaultLogger()->logError("Missing 'mediaBaseUrl' section from config");
            exit;
        }

        return self::getInstance(
            className: 'MediaService',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Module/Article/Entity/RawFile.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/MediaType.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/MediaStatus.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/ImageSize.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Media.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Service/MediaService.php';
                return new MediaService(
                    logger: self::getArticleLogger(),
                    articleService: self::getArticleService(),
                    mediaDataLayer: self::getMediaDataLayer(),
                    imageService: self::getImageService(),
                    albumService: AlbumCore::getAlbumService(),
                    mediaBaseDir: self::getConfig()->mediaBaseDir,
                );
            },
        );
    }

    public static function getImageService(): ImageService
    {
        return self::getInstance(
            className: 'ImageService',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Module/Article/Entity/ImageExif.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/ImageSize.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Service/ImageService.php';
                return new ImageService(
                    logger: self::getArticleLogger(),
                );
            },
        );
    }

    public static function getTagDataLayer(): TagDataLayer
    {
        return self::getInstance(
            className: 'TagDataLayer',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Module/Article/DataLayer/TagDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Tag.php';
                return new TagDataLayer(
                    db: self::getDb(),
                    logger: self::getArticleLogger(),
                );
            },
        );
    }

    public static function getTagService(): TagService
    {
        return self::getInstance(
            className: 'TagService',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Module/Article/Service/TagService.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Tag.php';
                return new TagService(
                    logger: self::getArticleLogger(),
                    tagDataLayer:  self::getTagDataLayer(),
                );
            },
        );
    }

    public static function getFeedService(): FeedService
    {
        return self::getInstance(
            className: 'FeedService',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Module/Article/Service/FeedService.php';
                return new FeedService(
                    logger: self::getArticleLogger(),
                );
            },
        );
    }

    public static function getImageResizeApp(): ImageResizeApp
    {
        return self::getInstance(
            className: 'ImageResizeApp',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserJourneyStatus.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserStatus.php';
                require_once self::getPathRoot() . '/Core/Module/User/Value/UserRole.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/ImageSize.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Media.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/MediaDestroyed.php';
                require_once self::getPathRoot() . '/Core/App/LockManager.php';
                require_once self::getPathRoot() . '/Core/App/App.php';
                require_once self::getPathRoot() . '/Core/Module/Article/App/ImageResizeApp.php';

                return new ImageResizeApp(
                    logger: self::getArticleLogger(),
                    mediaService: self::getMediaService(),
                    imageService: self::getImageService(),
                );
            },
        );
    }
}
