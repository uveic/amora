<?php

use Amora\Core\Entity\Response\HtmlHomepageResponseData;
use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData|HtmlHomepageResponseData|HtmlResponseDataAdmin $responseData */

$canonical = '';

if (isset($responseData->article)) {
    $articleUrl = UrlBuilderUtil::buildPublicArticlePath(
        path: $responseData->article->path,
        language: $responseData->siteLanguage,
    );

    $canonical = $responseData->siteUrl === $articleUrl
        ? ''
        : '  <link rel="canonical" href="' . $articleUrl . '">' . PHP_EOL;
}

?>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="<?=$responseData->getPageDescription()?>">
  <meta property="og:site_name" content="<?=$responseData->siteName?>">
  <meta property="og:title" content="<?=$responseData->getPageTitle()?>">
  <meta property="og:description" content="<?=$responseData->getPageDescription()?>">
  <meta property="og:image" content="<?=$responseData->siteImagePath?>">
  <meta property="og:url" content="<?=$responseData->siteUrl?>">
  <meta property="og:type" content="website">
  <meta property="og:updated_time" content="<?=$responseData->lastUpdatedTimestamp?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta property="twitter:domain" content="<?=$responseData->getSiteDomain()?>">
  <meta property="twitter:url" content="<?=$responseData->siteUrl?>">
  <meta name="twitter:title" content="<?=$responseData->getPageTitle()?>">
  <meta name="twitter:description" content="<?=$responseData->getPageDescription()?>">
  <meta name="twitter:image" content="<?=$responseData->siteImagePath?>">
  <title><?=$responseData->getPageTitle()?></title>
  <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon/favicon-16x16.png">
  <link rel="manifest" href="/manifest.json">
  <link rel="alternate" type="application/rss+xml" title="<?=$responseData->siteName?>" href="<?=UrlBuilderUtil::buildPublicRssUrl()?>">
  <link rel="alternate" href="<?=$responseData->siteUrl?>" hreflang="x-default">
<?=$canonical?>
