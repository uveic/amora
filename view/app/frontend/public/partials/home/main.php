<?php

use Amora\Core\Entity\Response\HtmlHomepageResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlHomepageResponseData $responseData */

$homepageContent = $responseData->homepageContent;
$editLink = $homepageContent && $responseData->request->session && $responseData->request->session->isAdmin()
    ? '<p><a href="' . UrlBuilderUtil::buildBackofficeArticleUrl($homepageContent->language, $homepageContent->id) . '">' . strtolower($responseData->getLocalValue('globalEdit')) . '</a></p>'
    : '';
?>
  <article class="home-main">
<?php if ($homepageContent?->title) { ?>
    <h1><?=$homepageContent->title?></h1>
<?php } ?>
    <?=$homepageContent?->html?>
    <?=$editLink?>
  </article>
