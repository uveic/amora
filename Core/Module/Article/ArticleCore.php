<?php

namespace Amora\Core\Module\Article;

use Amora\Core\Core;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Article\Datalayer\ArticleDataLayer;
use Amora\Core\Module\Article\Datalayer\TagDataLayer;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Datalayer\MediaDataLayer;
use Amora\Core\Module\Article\Service\ImageService;
use Amora\Core\Module\Article\Service\MediaService;
use Amora\Core\Module\Article\Service\XmlService;
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
                require_once self::getPathRoot() . '/Core/Module/User/Datalayer/UserDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Datalayer/ArticleDataLayer.php';
                return new ArticleDataLayer(
                    db: self::getDb(),
                    logger: self::getArticleLogger(),
                    mediaDataLayer:  self::getMediaDataLayer(),
                    tagDataLayer:  self::getTagDataLayer(),
                );
            },
            isSingleton: true,
        );
    }

    public static function getArticleService(): ArticleService
    {
        return self::getInstance(
            className: 'ArticleService',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Util/Helper/ArticleEditHtmlGenerator.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/ArticleStatus.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/ArticleSectionType.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/MediaType.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/MediaStatus.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Article.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/ArticleUri.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/ArticleSection.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Service/ArticleService.php';
                return new ArticleService(
                    logger:  self::getArticleLogger(),
                    articleDataLayer:  self::getArticleDataLayer(),
                    tagDataLayer:  self::getTagDataLayer(),
                );
            },
            isSingleton: true,
        );
    }

    public static function getMediaDataLayer(): MediaDataLayer
    {
        return self::getInstance(
            className: 'MediaDataLayer',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Media.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Article.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Datalayer/MediaDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Datalayer/ArticleDataLayer.php';
                return new MediaDataLayer(
                    db: self::getDb(),
                    logger:  self::getArticleLogger(),
                );
            },
            isSingleton: true,
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
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Media.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Service/MediaService.php';
                return new MediaService(
                    logger: self::getArticleLogger(),
                    articleService: self::getArticleService(),
                    mediaDataLayer: self::getMediaDataLayer(),
                    imageService: self::getImageService(),
                    mediaBaseDir: self::getConfig()->mediaBaseDir,
                );
            },
            isSingleton: true,
        );
    }

    public static function getImageService(): ImageService
    {
        return self::getInstance(
            className: 'ImageService',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Module/Article/Entity/ImageExif.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Service/ImageService.php';
                return new ImageService(
                    logger: self::getArticleLogger(),
                );
            },
            isSingleton: true,
        );
    }

    public static function getTagDataLayer(): TagDataLayer
    {
        return self::getInstance(
            className: 'TagDataLayer',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Module/Article/Datalayer/TagDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Tag.php';
                return new TagDataLayer(
                    db: self::getDb(),
                    logger: self::getArticleLogger(),
                );
            },
            isSingleton: true,
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
            isSingleton: true,
        );
    }

    public static function getXmlService(): XmlService
    {
        return self::getInstance(
            className: 'XmlService',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Module/Article/Service/XmlService.php';
                return new XmlService(
                    logger: self::getArticleLogger(),
                );
            },
            isSingleton: true,
        );
    }
}
