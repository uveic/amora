<?php

use Amora\Core\Model\Response\HtmlResponseDataAbstract;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAbstract $responseData */

?>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="<?=$this->e($responseData->getPageDescription())?>">
  <meta property="og:site_name" content="<?=$this->e($responseData->getSiteName())?>">
  <meta property="og:title" content="<?=$this->e($responseData->getPageTitleWithoutSiteName())?>">
  <meta property="og:description" content="<?=$this->e($responseData->getPageDescription())?>">
  <meta property="og:image" content="<?=$responseData->getSiteImageUri()?>">
  <meta property="og:url" content="<?=$responseData->getSiteUrl()?>">
  <meta property="og:type" content="website">
  <meta property="og:updated_time" content="<?=$responseData->getLasUpdatedTimestamp()?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta property="twitter:domain" content="<?=$responseData->getSiteDomain()?>">
  <meta property="twitter:url" content="<?=$responseData->getSiteUrl()?>">
  <meta name="twitter:title" content="<?=$this->e($responseData->getPageTitleWithoutSiteName())?>">
  <meta name="twitter:description" content="<?=$this->e($responseData->getPageDescription())?>">
  <meta name="twitter:image" content="<?=$responseData->getSiteImageUri()?>">
  <title><?=$this->e($responseData->getPageTitle())?></title>
  <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon/favicon-16x16.png">
  <link rel="manifest" href="/img/favicon/site.webmanifest">
  <link rel="alternate" type="application/rss+xml" title="<?=$this->e($responseData->getSiteName())?>" href="<?=UrlBuilderUtil::buildPublicRssUrl()?>" />
  <link rel="alternate" href="<?=$responseData->getSiteUrl()?>" hreflang="x-default">
  <link href="/css/style.css" rel="stylesheet" type="text/css">
</head>
