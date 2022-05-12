<?php

namespace Amora\Core\Module\Article;

use Amora\Core\Core;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Article\Datalayer\ArticleDataLayer;
use Amora\Core\Module\Article\Datalayer\TagDataLayer;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Datalayer\ImageDataLayer;
use Amora\Core\Module\Article\Service\ImageResizeService;
use Amora\Core\Module\Article\Service\ImageService;
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
        $db = self::getDb();
        $logger = self::getArticleLogger();
        $imageDataLayer = self::getImageDataLayer();
        $tagDataLayer = self::getTagDataLayer();

        return self::getInstance(
            className: 'ArticleDataLayer',
            factory: function () use ($db, $logger, $imageDataLayer, $tagDataLayer) {
                require_once self::getPathRoot() . '/Core/Module/User/Model/User.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Article.php';
                require_once self::getPathRoot() . '/Core/Module/User/Datalayer/UserDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Datalayer/ArticleDataLayer.php';
                return new ArticleDataLayer($db, $logger, $imageDataLayer, $tagDataLayer);
            },
            isSingleton: true,
        );
    }

    public static function getArticleService(): ArticleService
    {
        $logger = self::getArticleLogger();
        $articleDataLayer = self::getArticleDataLayer();
        $tagDataLayer = self::getTagDataLayer();

        return self::getInstance(
            className: 'ArticleService',
            factory: function () use ($logger, $articleDataLayer, $tagDataLayer) {
                require_once self::getPathRoot() . '/Core/Util/Helper/ArticleEditHtmlGenerator.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/ArticleStatus.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/ArticleSectionType.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/ArticleType.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Article.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/ArticleUri.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/ArticleSection.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Service/ArticleService.php';
                return new ArticleService($logger, $articleDataLayer, $tagDataLayer);
            },
            isSingleton: true,
        );
    }

    public static function getImageDataLayer(): ImageDataLayer
    {
        $db = self::getDb();
        $logger = self::getArticleLogger();

        return self::getInstance(
            className: 'ImageDataLayer',
            factory: function () use ($db, $logger) {
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Image.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Article.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Datalayer/ImageDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Datalayer/ArticleDataLayer.php';
                return new ImageDataLayer($db, $logger);
            },
            isSingleton: true,
        );
    }

    public static function getImageService(): ImageService
    {
        if (empty(self::getConfig()->mediaBaseDir)) {
            self::getDefaultLogger()->logError("Missing 'mediaBaseDir' section from config");
            exit;
        }

        $logger = self::getArticleLogger();
        $imageDataLayer = self::getImageDataLayer();
        $imageResizeService = self::getImageResizeService();
        $mediaBaseDir = self::getConfig()->mediaBaseDir;

        return self::getInstance(
            className: 'ImageService',
            factory: function () use (
                $logger,
                $imageDataLayer,
                $imageResizeService,
                $mediaBaseDir,
            ) {
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Image.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/ImagePath.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Service/ImageService.php';
                return new ImageService(
                    $logger,
                    $imageDataLayer,
                    $imageResizeService,
                    $mediaBaseDir,
                );
            },
            isSingleton: true,
        );
    }

    public static function getImageResizeService(): ImageResizeService
    {
        if (empty(self::getConfig()->mediaBaseDir)) {
            self::getDefaultLogger()->logError("Missing 'mediaBaseDir' section from config");
            exit;
        }

        if (empty(self::getConfig()->mediaBaseUrl)) {
            self::getDefaultLogger()->logError("Missing 'mediaBaseUrl' section from config");
            exit;
        }

        $logger = self::getArticleLogger();
        $mediaBaseDir = self::getConfig()->mediaBaseDir;
        $mediaBaseUrl = self::getConfig()->mediaBaseUrl;

        return self::getInstance(
            className: 'ImageResizeService',
            factory: function () use ($logger, $mediaBaseDir, $mediaBaseUrl) {
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Image.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/ImagePath.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Service/ImageResizeService.php';
                return new ImageResizeService($logger, $mediaBaseDir, $mediaBaseUrl);
            },
            isSingleton: true,
        );
    }

    public static function getTagDataLayer(): TagDataLayer
    {
        $db = self::getDb();
        $logger = self::getArticleLogger();

        return self::getInstance(
            className: 'TagDataLayer',
            factory: function () use ($db, $logger) {
                require_once self::getPathRoot() . '/Core/Module/Article/Datalayer/TagDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Tag.php';
                return new TagDataLayer($db, $logger);
            },
            isSingleton: true,
        );
    }

    public static function getTagService(): TagService
    {
        $logger = self::getArticleLogger();
        $tagDatalayer = self::getTagDataLayer();

        return self::getInstance(
            className: 'TagService',
            factory: function () use ($logger, $tagDatalayer) {
                require_once self::getPathRoot() . '/Core/Module/Article/Service/TagService.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Tag.php';
                return new TagService($logger, $tagDatalayer);
            },
            isSingleton: true,
        );
    }

    public static function getXmlService(): XmlService
    {
        $logger = self::getArticleLogger();

        return self::getInstance(
            className: 'XmlService',
            factory: function () use ($logger) {
                require_once self::getPathRoot() . '/Core/Module/Article/Service/XmlService.php';
                return new XmlService($logger);
            },
            isSingleton: true,
        );
    }
}
