<?php

use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */
$previousArticle = $responseData->previousBlogPost;
$nextArticle = $responseData->nextBlogPost;

if (!$previousArticle && !$nextArticle) {
  return;
}

$previousArticleHtml = $previousArticle
    ? '<a href="' . UrlBuilderUtil::buildPublicArticlePath($previousArticle->path, $responseData->siteLanguage) . '">« ' . $previousArticle->title . '</a>'
    : '';

$nextArticleHtml = $nextArticle
    ? '<a class="text-right" href="' . UrlBuilderUtil::buildPublicArticlePath($nextArticle->path, $responseData->siteLanguage) . '">' . $nextArticle->title . ' »</a>'
    : '';

?>
    <p class="article-blog-bottom">
      <?=$previousArticleHtml ?>
      <?=$nextArticleHtml ?>
    </p>
