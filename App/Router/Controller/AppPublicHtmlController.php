<?php

namespace Amora\App\Router;

use Amora\Core\Model\Request;

final class AppPublicHtmlController extends AppPublicHtmlControllerAbstract
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function authenticate(Request $request): bool
    {
        return true;
    }
}
