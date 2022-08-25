<?php

use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */
$isAdmin = $responseData->request->session && $responseData->request->session->isAdmin();

if (!$isAdmin) {
  return;
}

$article = $responseData->getFirstArticle();
$editUrlHtml = '<a href="' . UrlBuilderUtil::buildBackofficeArticleUrl($responseData->siteLanguage, $article->id) . '">' . strtolower($responseData->getLocalValue('globalEdit')) . '</a>';

$icon = ArticleEditHtmlGenerator::generateArticlePublishedIconHtml(
    article: $article,
);

?>
    <p class="article-blog-info"><?=$editUrlHtml?></p>
