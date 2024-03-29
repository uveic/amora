<?php

use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */
$isAdmin = $responseData->request->session && $responseData->request->session->isAdmin();

if (!$isAdmin) {
  return;
}

$article = $responseData->article;
$editUrlHtml = '<a href="' . UrlBuilderUtil::buildBackofficeArticleUrl($responseData->siteLanguage, $article->id) . '">' . strtolower($responseData->getLocalValue('globalEdit')) . '</a>';

$icon = ArticleHtmlGenerator::generateArticlePublishedIconHtml(
    article: $article,
);

?>
    <p class="article-blog-info"><?=$editUrlHtml?></p>
