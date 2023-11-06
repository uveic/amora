<?php

namespace Amora\Core\Module\Album;

use Amora\Core\Core;
use Amora\Core\Database\MySqlDb;
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
            factory: function () {
                require_once self::getPathRoot() . '/Core/Module/User/Model/User.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Model/Album.php';
                require_once self::getPathRoot() . '/Core/Module/User/DataLayer/UserDataLayer.php';
                require_once self::getPathRoot() . '/Core/Module/Album/DataLayer/AlbumDataLayer.php';
                return new AlbumDataLayer(
                    db: self::getDb(),
                    logger: self::getAlbumLogger(),
                    mediaDataLayer:  self::getMediaDataLayer(),
                    tagDataLayer:  self::getTagDataLayer(),
                );
            },
            isSingleton: true,
        );
    }

    public static function getAlbumService(): AlbumService
    {
        return self::getInstance(
            className: 'AlbumService',
            factory: function () {
                require_once self::getPathRoot() . '/Core/Util/Helper/AlbumHtmlGenerator.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Value/PageContentType.php';
                require_once self::getPathRoot() . '/App/Value/AppPageContentType.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Model/PageContent.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Value/AlbumStatus.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Value/AlbumSectionType.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Value/MediaType.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Value/MediaStatus.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Model/Album.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Model/AlbumPath.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Model/AlbumSection.php';
                require_once self::getPathRoot() . '/Core/Module/Album/Service/AlbumService.php';
                return new AlbumService(
                    logger:  self::getAlbumLogger(),
                    articleDataLayer:  self::getAlbumDataLayer(),
                    tagDataLayer:  self::getTagDataLayer(),
                );
            },
            isSingleton: true,
        );
    }
}
