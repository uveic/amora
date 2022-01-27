<?php

use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */
$previousArticle = $responseData->getPreviousBlogPost();
$nextArticle = $responseData->getNextBlogPost();

if (!$previousArticle && !$nextArticle) {
  return;
}

$previousArticleHtml = $previousArticle
    ? '<a href="' . UrlBuilderUtil::buildPublicArticleUrl($previousArticle->getUri(), $responseData->getSiteLanguage()) . '">« ' . $previousArticle->getTitle() . '</a>'
    : '';

$nextArticleHtml = $nextArticle
    ? '<a class="text-right" href="' . UrlBuilderUtil::buildPublicArticleUrl($nextArticle->getUri(), $responseData->getSiteLanguage()) . '">' . $nextArticle->getTitle() . ' »</a>'
    : '';

?>
    <p class="article-blog-bottom">
      <?=$previousArticleHtml ?>
      <?=$nextArticleHtml ?>
    </p>
