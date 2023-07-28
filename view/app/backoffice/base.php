<?php

use Amora\App\Value\AppMenu;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;

/** @var HtmlResponseDataAdmin $responseData */

$menuItems = AppMenu::getAdmin(
    language: $responseData->siteLanguage,
    username: $responseData->request->session->user->getNameOrEmail(),
    includeUserDashboardLink: false,
);

?>
<!DOCTYPE html>
<html lang="<?=$this->e(strtolower($responseData->siteLanguage->value))?>">
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
  <link href="/css/editor/medium-editor.min.css" rel="stylesheet" >
  <link href="/css/editor/custom-theme.css" rel="stylesheet">
  <link href="/css/navbar-004.css" rel="stylesheet" type="text/css">
  <link href="/css/style-205.css" rel="stylesheet" type="text/css">
  <link href="/css/backoffice-202.css" rel="stylesheet" type="text/css">
</head>
<body>
<?=$this->insert('../../core/backoffice/partials/navbar', ['responseData' => $responseData, 'menuItems' => $menuItems])?>
<?=$this->section('content')?>
<?=$this->insert('../../core/backoffice/partials/footer')?>
</body>
</html>
