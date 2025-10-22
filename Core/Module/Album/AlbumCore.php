<?php

namespace Amora\Core\Module\Album;

use Amora\Core\Core;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Album\DataLayer\AlbumDataLayer;
use Amora\Core\Module\Album\Service\AlbumService;

class AlbumCore extends Core
{
    public static function getDb(): MySqlDb
    {
        return self::getCoreDb();
    }

    public static function getAlbumLogger(): Logger
    {
        return self::getLogger();
    }

    public static function getAlbumDataLayer(): AlbumDataLayer
    {
        return self::getInstance(
            className: 'AlbumDataLayer',
            factory: static function () {
                require_once self::getPathRoot() . '/Core/Module/User/Model/User.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Model/CollectionMedia.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Model/Collection.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Model/Album.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Model/AlbumSlug.php';
                require_once self::getPathRoot() . '/Core/Module/User/DataLayer/UserDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/Article/DataLayer/MediaDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/Album/DataLayer/AlbumDataLayer.php';
                return new AlbumDataLayer(
                    db: self::getDb(),
                    logger: self::getAlbumLogger(),
                    mediaDataLayer: ArticleCore::getMediaDataLayer(),
                );
            },
        );
    }

    public static function getAlbumService(): AlbumService
    {
        return self::getInstance(
            className: 'AlbumService',
            factory: static function () {
                require_once self::getPathRoot() . '/Core/Module/Article/Value/MediaType.php';
                require_once self::getPathRoot() . '/Core/Module/Article/Value/MediaStatus.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Value/AlbumStatus.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Value/Template.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Model/CollectionMedia.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Model/Collection.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Model/Album.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Model/AlbumSlug.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Service/AlbumService.php';
                return new AlbumService(
                    albumDataLayer:  self::getAlbumDataLayer(),
                );
            },
        );
    }
}
