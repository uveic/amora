<?php

use Amora\Core\Entity\Response\HtmlHomepageResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlHomepageResponseData $responseData */

$pageContent = $responseData->pageContent;
$editLink = $pageContent && $responseData->request->session?->isAdmin()
    ? '<p><a href="' . UrlBuilderUtil::buildBackofficeContentEditUrl($pageContent->language, $pageContent->type, $pageContent->language) . '">' . strtolower($responseData->getLocalValue('globalEdit')) . '</a></p>'
    : '';
?>
  <article class="home-main">
<?php if ($pageContent?->titleHtml) { ?>
    <h1><?=$pageContent->titleHtml?></h1>
<?php } ?>
    <?=$pageContent?->contentHtml?>
    <?=$editLink?>
  </article>
