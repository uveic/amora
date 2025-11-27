<?php

namespace Amora\App\Router;

use Amora\Core\Entity\Request;

final readonly class AppPublicApiController extends AppPublicApiControllerAbstract
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
