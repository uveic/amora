<?php

use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;

/** @var HtmlResponseData $responseData */
$article = $responseData->getFirstArticle();

if (!$article || !$article->title) {
  return '';
}

$publishedOnDate = DateUtil::formatDate(
    date: $article->publishOn ?? $article->updatedAt,
    lang: $responseData->siteLanguageIsoCode,
    includeWeekDay: false,
    includeTime: false,
);

$icon = ArticleEditHtmlGenerator::generateArticlePublishedIconHtml(
    article: $article,
);

?>
    <h1><?=$icon . $article->title?></h1>
    <p class="article-blog-info"><?=$this->e($publishedOnDate)?></p>
