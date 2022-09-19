<?php

use Amora\Core\Entity\Response\HtmlHomepageResponseData;
use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Entity\Response\HtmlResponseDataAuthorised;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData|HtmlHomepageResponseData|HtmlResponseDataAuthorised $responseData */

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
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="<?=$this->e($responseData->getPageDescription())?>">
  <meta property="og:site_name" content="<?=$this->e($responseData->siteName)?>">
  <meta property="og:title" content="<?=$this->e($responseData->pageTitleWithoutSiteName)?>">
  <meta property="og:description" content="<?=$this->e($responseData->getPageDescription())?>">
  <meta property="og:image" content="<?=$responseData->getSiteImagePath()?>">
  <meta property="og:url" content="<?=$responseData->siteUrl?>">
  <meta property="og:type" content="website">
  <meta property="og:updated_time" content="<?=$responseData->lastUpdatedTimestamp?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta property="twitter:domain" content="<?=$responseData->getSiteDomain()?>">
  <meta property="twitter:url" content="<?=$responseData->siteUrl?>">
  <meta name="twitter:title" content="<?=$this->e($responseData->pageTitleWithoutSiteName)?>">
  <meta name="twitter:description" content="<?=$this->e($responseData->getPageDescription())?>">
  <meta name="twitter:image" content="<?=$responseData->getSiteImagePath()?>">
  <title><?=$this->e($responseData->getPageTitle())?></title>
  <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon/favicon-16x16.png">
  <link rel="manifest" href="/manifest.json">
  <link rel="alternate" type="application/rss+xml" title="<?=$this->e($responseData->siteName)?>" href="<?=UrlBuilderUtil::buildPublicRssUrl()?>" />
  <link rel="alternate" href="<?=$responseData->siteUrl?>" hreflang="x-default">
<?=$canonical?>
  <link href="/css/style-001.css" rel="stylesheet" type="text/css">
  <link href="/css/app/style.css" rel="stylesheet" type="text/css">
</head>
