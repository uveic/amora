<?php

use Amora\Core\Model\Response\HtmlHomepageResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlHomepageResponseData $responseData */

$homepageContent = $responseData->homepageContent;
$editLink = $homepageContent && $responseData->request->session && $responseData->request->session->isAdmin()
    ? '<p class="no-margin"><a href="' . UrlBuilderUtil::buildBackofficeArticleUrl($homepageContent->language, $homepageContent->id) . '">' . strtolower($responseData->getLocalValue('globalEdit')) . '</a></p>'
    : '';
?>
  <section class="home-main">
    <?=$homepageContent ? $homepageContent->contentHtml : '';?>
    <?=$editLink;?>

  </section>
