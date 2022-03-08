<?php

use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */
$article = $responseData->getFirstArticle();

if (!$article || !$article->title) {
  return;
}

$canEdit = $responseData->request->session && $responseData->request->session->isAdmin();
$editUrlHtml = $canEdit
    ? '<a class="article-edit" href="' . UrlBuilderUtil::buildBackofficeArticleUrl($responseData->siteLanguage, $article->id) . '">' . strtolower($responseData->getLocalValue('globalEdit')) . '</a>'
    : '';

$publishedOnDate = DateUtil::formatDate(
    date: $article->publishOn ?? $article->updatedAt,
    lang: $responseData->siteLanguage,
    includeWeekDay: false,
    includeTime: false,
);

$icon = ArticleEditHtmlGenerator::generateArticlePublishedIconHtml(
    article: $article,
);

?>
    <h1><?=$icon . $article->title?></h1>
    <p class="article-blog-info"><?=$publishedOnDate . $editUrlHtml?></p>
