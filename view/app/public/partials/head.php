<?php

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAbstract $responseData */

$canonicalUrl = null;

if (isset($responseData->article)) {
    $articleUrl = UrlBuilderUtil::buildPublicArticlePath(
        path: $responseData->article->path,
        language: $responseData->siteLanguage,
    );

    $canonicalUrl = $responseData->siteUrl === $articleUrl ? null : $articleUrl;
}

if (!$canonicalUrl) {
    $canonicalUrl = $responseData->siteUrl;
}

?>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="<?=$responseData->getPageDescription()?>">
  <meta property="og:locale" content="<?=$responseData->siteLanguage->getLocale()?>">
  <meta property="og:site_name" content="<?=$responseData->siteName?>">
  <meta property="og:title" content="<?=$responseData->getPageTitle()?>">
  <meta property="og:description" content="<?=$responseData->getPageDescription()?>">
  <meta property="og:image" content="<?=$responseData->siteImageUrl?>">
  <meta property="og:url" content="<?=$responseData->siteUrl?>">
  <meta property="og:type" content="website">
  <meta property="og:updated_time" content="<?=$responseData->lastUpdatedTimestamp?>">
<?php if ($responseData->themeColourHex) { ?>
  <meta name="theme-color" content="<?=$responseData->themeColourHex?>">
<?php } ?>
  <meta name="thumbnail" content="<?=$responseData->siteImageUrl?>">
  <title><?=$responseData->getPageTitle()?></title>
  <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="512x512" href="/img/favicon/android-chrome-512x512.png">
  <link rel="icon" type="image/png" sizes="192x192" href="/img/favicon/android-chrome-192x192.png">
  <link rel="icon" type="image/png" sizes="96x96" href="/img/favicon/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon/favicon-16x16.png">
  <link rel="icon" href="/favicon.ico">
  <link rel="alternate" type="application/rss+xml" title="<?=$responseData->siteName?>" href="<?=UrlBuilderUtil::buildPublicRssUrl()?>">
  <link rel="alternate" type="application/feed+json" title="<?=$responseData->siteName?>" href="<?=UrlBuilderUtil::buildPublicJsonFeedUrl()?>">
<?php
    $baseUrlWithLanguage = UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage);
    $baseUrlWithoutLanguage = UrlBuilderUtil::buildBaseUrlWithoutLanguage();
    $pathWithoutLanguage = $responseData->request->getPathWithoutLanguageAsString();
    if ($pathWithoutLanguage === 'home') {
        $pathWithoutLanguage = '';
    }

    if ($responseData->siteUrl === $baseUrlWithoutLanguage || $responseData->siteUrl === $baseUrlWithLanguage) {
        echo '  <link rel="alternate" href="' . $baseUrlWithoutLanguage . '" hreflang="x-default">' . PHP_EOL;
    } elseif ($pathWithoutLanguage) {
        echo '  <link rel="alternate" href="' . $baseUrlWithoutLanguage . '/' . $pathWithoutLanguage . '" hreflang="x-default">' . PHP_EOL;
    }

    /** @var Language $enabledSiteLanguage */
    foreach (Core::getEnabledSiteLanguages() as $enabledSiteLanguage) {
        $url = $baseUrlWithoutLanguage . '/' . strtolower($enabledSiteLanguage->value) . ($pathWithoutLanguage ? '/' . $pathWithoutLanguage : '');
        if ($url !== $responseData->siteUrl) {
            echo '  <link rel="alternate" href="' . $url . '" hreflang="' . strtolower($enabledSiteLanguage->value) . '">' . PHP_EOL;
        }
    }
?>
  <link rel="canonical" href="<?=$canonicalUrl?>">
