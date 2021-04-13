<?php

namespace Amora\Core\Module\Article;

use Amora\Core\Core;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Logger;
use Amora\Core\Module\Article\Datalayer\ArticleDataLayer;
use Amora\Core\Module\Article\Datalayer\TagDataLayer;
use Amora\Core\Module\Article\Service\ArticleService;
use Amora\Core\Module\Article\Datalayer\ImageDataLayer;
use Amora\Core\Module\Article\Service\ImageResizeService;
use Amora\Core\Module\Article\Service\ImageService;
use Amora\Core\Module\Article\Service\TagService;

class ArticleCore extends Core
{
    public static function getDb(): MySqlDb
    {
        return self::getCoreDb();
    }

    public static function getArticleLogger(): Logger
    {
        return self::getLogger('ArticleCoreModuleLogger');
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
                require_once self::getPathRoot() . '/Core/Module/User/Model/User.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Article.php';
                require_once self::getPathRoot() . '/Core/Module/User/Datalayer/UserDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Datalayer/ArticleDataLayer.php';
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
                require_once self::getPathRoot() . '/Core/Module/Article/Value/ArticleStatus.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/ArticleType.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Article.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/ArticleSection.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Service/ArticleService.php';
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
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Image.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Article.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Datalayer/ImageDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Datalayer/ArticleDataLayer.php';
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
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Image.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/ImagePath.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Service/ImageService.php';
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
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Image.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/ImagePath.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Service/ImageResizeService.php';
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
                require_once self::getPathRoot() . '/Core/Module/Article/Datalayer/TagDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Tag.php';
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
                require_once self::getPathRoot() . '/Core/Module/Article/Service/TagService.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Model/Tag.php';
                return new TagService($logger, $tagDatalayer);
            },
            true
        );
    }
}
