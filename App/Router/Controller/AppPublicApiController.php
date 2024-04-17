<?php

namespace Amora\App\Router;

use Amora\Core\Entity\Request;
use Amora\Core\Module\Album\Service\AlbumService;
use Amora\Core\Module\Article\Service\ArticleService;

final class AppPublicApiController extends AppPublicApiControllerAbstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function authenticate(Request $request): bool
    {
        return true;
    }
}
