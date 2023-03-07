<?php

use Amora\Core\Entity\Response\HtmlHomepageResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlHomepageResponseData $responseData */

$pageContent = $responseData->pageContent;
$editLink = $pageContent && $responseData->request->session?->isAdmin()
    ? '<p><a href="' . UrlBuilderUtil::buildBackofficeContentEditUrl($pageContent->language, $pageContent->id) . '">' . strtolower($responseData->getLocalValue('globalEdit')) . '</a></p>'
    : '';
?>
  <article class="home-main">
<?php if ($pageContent?->title) { ?>
    <h1><?=$pageContent->title?></h1>
<?php } ?>
    <?=$pageContent?->html?>
    <?=$editLink?>
  </article>
