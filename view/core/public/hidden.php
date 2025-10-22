<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAbstract $responseData */

$baseUrl = parse_url(UrlBuilderUtil::buildBaseUrlWithoutLanguage(), PHP_URL_HOST);
?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="<?=$responseData->pageDescription?>">
  <meta property="og:site_name" content="<?=$responseData->siteName?>">
  <meta property="og:title" content="<?=$responseData->getPageTitle()?>">
  <meta property="og:description" content="<?=$responseData->pageDescription?>">
  <meta property="og:image" content="<?=$responseData->siteImageUrl?>">
  <meta property="og:url" content="<?=$responseData->siteUrl?>">
  <meta property="og:type" content="website">
  <meta property="og:updated_time" content="<?=$responseData->lastUpdatedTimestamp?>">
<?php if ($responseData->themeColourHex) { ?>
  <meta name="theme-color" content="<?=$responseData->themeColourHex?>"/>
<?php } ?>
  <meta name="thumbnail" content="<?=$responseData->siteImageUrl?>">
  <title><?=$responseData->getPageTitle()?></title>
  <link href="/css/hidden.css?v=000" rel="stylesheet" type="text/css">
</head>
<body>
  <main class="home">
    <p><?=$baseUrl?></p>
  </main>
</body>
</html>