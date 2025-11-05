<?php

use Amora\App\Value\AppMenu;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */

$navBarArguments = [
    'responseData' => $responseData,
    'menuItems' => AppMenu::getCustomer(
        language: $responseData->siteLanguage,
        username: $responseData->request->session->user->getNameOrEmail(),
        includeAdminLink: $responseData->request->session?->isAdmin() ?? false,
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
  <meta name="description" content="<?=$responseData->pageDescription?>">
  <title><?=$responseData->getPageTitle()?></title>
  <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="512x512" href="/img/favicon/favicon-512x512.png">
  <link rel="icon" type="image/png" sizes="192x192" href="/img/favicon/favicon-192x192.png">
  <link rel="icon" type="image/png" sizes="96x96" href="/img/favicon/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon/favicon-16x16.png">
  <link rel="manifest" href="/manifest.json">
  <link href="/css/navbar-base.css?v=000" rel="stylesheet" type="text/css">
  <link href="/css/shared-base.css?v=000" rel="stylesheet" type="text/css">
</head>
<body>
<?=$this->insert('../../../core/backoffice/partials/navbar', $navBarArguments)?>
  <main>
    <section>
      <div id="feedback" class="feedback null"></div>
      <div class="m-r-1 m-l-1">
        <h1><?=$responseData->getLocalValue('navAuthorisedDashboard')?></h1>
      </div
      <div>

      </div>
    </section>
  </main>
  <footer>
    <a href="<?=UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->siteLanguage)?>"><?=$responseData->getLocalValue('navAdministrator')?></a>
  </footer>
</body>
</html>
