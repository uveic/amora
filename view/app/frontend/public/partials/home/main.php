<?php

use Amora\Core\Entity\Response\AppHtmlHomepageResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var AppHtmlHomepageResponseData $responseData */

$pageContent = $responseData->pageContent;
$editLink = $pageContent && $responseData->request->session?->isAdmin()
    ? '<p><a href="' . UrlBuilderUtil::buildBackofficeContentEditUrl($responseData->siteLanguage, $pageContent->type, $pageContent->language) . '">' . strtolower($responseData->getLocalValue('globalEdit')) . '</a></p>'
    : '';
?>
  <article class="home-main">
<?php if ($pageContent?->title) { ?>
    <h1><?=$pageContent->title?></h1>
<?php } ?>
    <?=$pageContent?->contentHtml?>
    <?=$editLink?>
  </article>
