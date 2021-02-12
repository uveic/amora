<?php

namespace uve\core\module\article;

use uve\core\Core;
use uve\core\database\MySqlDb;
use uve\core\Logger;
use uve\core\module\article\datalayer\ArticleDataLayer;
use uve\core\module\article\datalayer\TagDataLayer;
use uve\core\module\article\service\ArticleService;
use uve\core\module\article\datalayer\ImageDataLayer;
use uve\core\module\article\service\ImageResizeService;
use uve\core\module\article\service\ImageService;
use uve\core\module\article\service\TagService;

class ArticleCore extends Core
{
    public static function getDb(): MySqlDb
    {
        return self::getCoreDb();
    }

    public static function getArticleLogger(): Logger
    {
        return self::getLogger('article_core_module_logger');
    }

    public static function getArticleDataLayer(): ArticleDataLayer
    {
        $db = self::getDb();
        $logger = self::getArticleLogger();
        $imageDataLayer = self::getImageDataLayer();
        $tagDataLayer = self::getTagDataLayer();

        return self::getInstance(
            'ArticleDataLayer',
            function () use ($db, $logger, $imageDataLayer, $tagDataLayer) {
                require_once self::getPathRoot() . '/core/module/user/model/User.php';
                require_once self::getPathRoot() . '/core/module/article/model/Article.php';
                require_once self::getPathRoot() . '/core/module/user/datalayer/UserDataLayer.php';
                require_once self::getPathRoot() . '/core/module/article/datalayer/ArticleDataLayer.php';
                return new ArticleDataLayer($db, $logger, $imageDataLayer, $tagDataLayer);
            },
            true
        );
    }

    public static function getArticleService(): ArticleService
    {
        $logger = self::getArticleLogger();
        $articleDataLayer = self::getArticleDataLayer();
        $tagDataLayer = self::getTagDataLayer();
        $imageService = self::getImageService();

        return self::getInstance(
            'ArticleService',
            function () use ($logger, $articleDataLayer, $tagDataLayer, $imageService) {
                require_once self::getPathRoot() . '/core/model/util/QueryOptions.php';
                require_once self::getPathRoot() . '/core/module/article/value/ArticleStatus.php';
                require_once self::getPathRoot() . '/core/module/article/value/ArticleType.php';
                require_once self::getPathRoot() . '/core/module/article/model/Article.php';
                require_once self::getPathRoot() . '/core/module/article/model/ArticleSection.php';
                require_once self::getPathRoot() . '/core/module/article/service/ArticleService.php';
                return new ArticleService($logger, $articleDataLayer, $tagDataLayer, $imageService);
            },
            true
        );
    }

    public static function getImageDataLayer(): ImageDataLayer
    {
        $db = self::getDb();
        $logger = self::getArticleLogger();

        return self::getInstance(
            'ImageDataLayer',
            function () use ($db, $logger) {
                require_once self::getPathRoot() . '/core/module/article/model/Image.php';
                require_once self::getPathRoot() . '/core/module/article/model/Article.php';
                require_once self::getPathRoot() . '/core/module/article/datalayer/ImageDataLayer.php';
                require_once self::getPathRoot() . '/core/module/article/datalayer/ArticleDataLayer.php';
                return new ImageDataLayer($db, $logger);
            },
            true
        );
    }

    public static function getImageService(): ImageService
    {
        if (empty(self::getConfigValue('mediaBaseDir'))) {
            self::getDefaultLogger()->logError("Missing 'mediaBaseDir' section from config");
            exit;
        }

        if (empty(self::getConfigValue('mediaBaseUrl'))) {
            self::getDefaultLogger()->logError("Missing 'mediaBaseUrl' section from config");
            exit;
        }

        $logger = self::getArticleLogger();
        $imageDataLayer = self::getImageDataLayer();
        $imageResizeService = self::getImageResizeService();
        $mediaBaseDir = self::getConfigValue('mediaBaseDir');
        $mediaBaseUrl = self::getConfigValue('mediaBaseUrl');

        return self::getInstance(
            'ImageService',
            function () use (
                $logger,
                $imageDataLayer,
                $imageResizeService,
                $mediaBaseDir,
                $mediaBaseUrl
            ) {
                require_once self::getPathRoot() . '/core/module/article/model/Image.php';
                require_once self::getPathRoot() . '/core/module/article/model/ImagePath.php';
                require_once self::getPathRoot() . '/core/module/article/service/ImageService.php';
                return new ImageService(
                    $logger,
                    $imageDataLayer,
                    $imageResizeService,
                    $mediaBaseDir,
                    $mediaBaseUrl);
            },
            true
        );
    }

    public static function getImageResizeService(): ImageResizeService
    {
        if (empty(self::getConfigValue('mediaBaseDir'))) {
            self::getDefaultLogger()->logError("Missing 'mediaBaseDir' section from config");
            exit;
        }

        if (empty(self::getConfigValue('mediaBaseUrl'))) {
            self::getDefaultLogger()->logError("Missing 'mediaBaseUrl' section from config");
            exit;
        }

        $logger = self::getArticleLogger();
        $mediaBaseDir = self::getConfigValue('mediaBaseDir');
        $mediaBaseUrl = self::getConfigValue('mediaBaseUrl');

        return self::getInstance(
            'ImageResizeService',
            function () use ($logger, $mediaBaseDir, $mediaBaseUrl) {
                require_once self::getPathRoot() . '/core/module/article/model/Image.php';
                require_once self::getPathRoot() . '/core/module/article/model/ImagePath.php';
                require_once self::getPathRoot() . '/core/module/article/service/ImageResizeService.php';
                return new ImageResizeService($logger, $mediaBaseDir, $mediaBaseUrl);
            },
            true
        );
    }

    public static function getTagDataLayer(): TagDataLayer
    {
        $db = self::getDb();
        $logger = self::getArticleLogger();

        return self::getInstance(
            'TagDataLayer',
            function () use ($db, $logger) {
                require_once self::getPathRoot() . '/core/module/article/datalayer/TagDataLayer.php';
                require_once self::getPathRoot() . '/core/module/article/model/Tag.php';
                return new TagDataLayer($db, $logger);
            },
            true
        );
    }

    public static function getTagService(): TagService
    {
        $logger = self::getArticleLogger();
        $tagDatalayer = self::getTagDataLayer();

        return self::getInstance(
            'TagService',
            function () use ($logger, $tagDatalayer) {
                require_once self::getPathRoot() . '/core/module/article/service/TagService.php';
                require_once self::getPathRoot() . '/core/module/article/model/Tag.php';
                return new TagService($logger, $tagDatalayer);
            },
            true
        );
    }
}
