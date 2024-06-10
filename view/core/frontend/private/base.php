<?php

use Amora\App\Value\AppMenu;
use Amora\Core\Entity\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

$navBarArguments = [
    'responseData' => $responseData,
    'menuItems' => $responseData->request->session->isAdmin()
        ? AppMenu::getAdmin(
            language: $responseData->siteLanguage,
            username: $responseData->request->session->user->getNameOrEmail(),
            includeUserDashboardLink: false,
        )
        : AppMenu::getCustomer(
            language: $responseData->siteLanguage,
            username: $responseData->request->session->user->getNameOrEmail(),
            whiteIcon: true,
        ),
    'siteLogoHtml' => $responseData->buildSiteLogoHtml(
        siteLanguage: $responseData->siteLanguage,
        siteContent: $responseData->siteContent ?? null,
        includeSubtitle: true,
        indentation: '    ',
    ),
];

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="<?=$this->e($responseData->getPageDescription())?>">
  <title><?=$this->e($responseData->getPageTitle())?></title>
  <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon/favicon-16x16.png">
  <link rel="manifest" href="/manifest.json">
  <link href="/css/navbar-base.css?v=000" rel="stylesheet" type="text/css">
  <link href="/css/shared-base.css?v=000" rel="stylesheet" type="text/css">
</head>
<body>
<?=$this->insert('../../backoffice/partials/navbar', ['responseData' => $responseData, 'menuItems' => $menuItems])?>
<?=$this->section('content')?>
<?=$this->insert('partials/footer')?>
</body>
</html>
