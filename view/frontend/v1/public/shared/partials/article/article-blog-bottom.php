<?php

use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */
$previousArticle = $responseData->previousBlogPost;
$nextArticle = $responseData->nextBlogPost;

if (!$previousArticle && !$nextArticle) {
  return;
}

$previousArticleHtml = $previousArticle
    ? '<a href="' . UrlBuilderUtil::buildPublicArticleUrl($previousArticle->uri, $responseData->getSiteLanguage()) . '">« ' . $previousArticle->title . '</a>'
    : '';

$nextArticleHtml = $nextArticle
    ? '<a class="text-right" href="' . UrlBuilderUtil::buildPublicArticleUrl($nextArticle->uri, $responseData->getSiteLanguage()) . '">' . $nextArticle->title . ' »</a>'
    : '';

?>
    <p class="article-blog-bottom">
      <?=$previousArticleHtml ?>
      <?=$nextArticleHtml ?>
    </p>
