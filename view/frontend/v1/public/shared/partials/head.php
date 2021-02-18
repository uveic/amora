<?php

use uve\core\model\response\HtmlResponseDataAbstract;

/** @var HtmlResponseDataAbstract $responseData */

?>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="<?=$this->e($responseData->getPageDescription())?>">
  <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon/favicon-16x16.png">
  <meta property="og:site_name" content="<?=$this->e($responseData->getSiteName())?>">
  <meta property="og:title" content="<?=$this->e($responseData->getPageTitleWithoutSiteName())?>">
  <meta property="og:description" content="<?=$this->e($responseData->getPageDescription())?>">
  <meta property="og:image" content="<?=$this->e($responseData->getSiteImageUri())?>">
  <meta property="og:url" content="<?=$this->e($responseData->getSiteUrl())?>">
  <meta property="og:type" content="website">
  <meta property="og:updated_time" content="<?=$this->e($responseData->getLasUpdatedTimestamp())?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta property="twitter:domain" content="<?=$this->e($responseData->getSiteDomain())?>">
  <meta property="twitter:url" content="<?=$this->e($responseData->getSiteUrl())?>">
  <meta name="twitter:title" content="<?=$this->e($responseData->getPageTitleWithoutSiteName())?>">
  <meta name="twitter:description" content="<?=$this->e($responseData->getPageDescription())?>">
  <meta name="twitter:image" content="<?=$this->e($responseData->getSiteImageUri())?>">
  <title><?=$this->e($responseData->getPageTitle())?></title>
  <link href="/css/style.css" rel="stylesheet" type="text/css">
</head>
