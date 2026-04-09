<?php

use Amora\App\Entity\AppHtmlHomepageResponseData;
use Amora\App\Util\AppUrlBuilderUtil;
use Amora\Core\Util\UrlBuilderUtil;

/** @var AppHtmlHomepageResponseData $responseData */

if (!$responseData->pageContent) {
    return '';
}

$isAdmin = $responseData->request->session?->isAdmin() ?? false;

$contentEditUrl = AppUrlBuilderUtil::buildBackofficeContentEditUrl(
    language: $responseData->siteLanguage,
    contentType: $responseData->pageContent->type,
    contentTypeLanguage: $responseData->pageContent->language,
);
?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<head>
<?=$this->insert('partials/head', ['responseData' => $responseData])?>
  <link href="/css/shared-base.css?v=107" rel="stylesheet" type="text/css">
</head>
<body>
  <header class="page-content-header">
    <a href="<?=UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage)?>"><?=$responseData->buildSiteLogoHtml(
            siteLanguage: $responseData->siteLanguage,
            siteContent: $responseData->siteContent ?? null,
            includeSubtitle: true,
            indentation: '    ',
        )?></a>
  </header>
  <main class="page-content">
<?php if ($responseData->pageContent->title) { ?>
    <h1 class="home-title"><?=$responseData->pageContent->title?></h1>
    <div class="home-title-separator"></div>
<?php }
    if ($isAdmin) {
?>
    <div class="m-b-1"><a href="<?=$contentEditUrl?>"><?=$responseData->getLocalValue('globalEdit')?></a></div>
<?php    }
if ($responseData->pageContent->contentHtml) { ?>
    <div><?=$responseData->pageContent->contentHtml?></div>
<?php } ?>
  </main>
</body>
</html>
