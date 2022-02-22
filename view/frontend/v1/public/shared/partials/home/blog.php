<?php

use Amora\Core\Model\Response\HtmlHomepageResponseData;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;

/** @var HtmlHomepageResponseData $responseData */

$isAdmin = $responseData->request->session && $responseData->request->session->isAdmin();

if (!$responseData->blogArticles) {
  return;
}

$articles = $responseData->blogArticles;
$itemsPerPage = $responseData->pagination->itemsPerPage;
$offset = $responseData->pagination->offset + $itemsPerPage;

?>
<section class="home-blog">
  <h1>Blog</h1>
  <div class="blog-items">
<?php
$previousYear = null;
/** @var Article $article */
foreach ($articles as $article) {
    if (!$article->isPublished() && !$isAdmin) {
        continue;
    }

    $title = $article->title ?: $responseData->getLocalValue('globalNoTitle');
    $publishedOn = $article->publishOn
        ? DateUtil::formatDate(
            date: $article->publishOn,
            lang: $responseData->siteLanguageIsoCode,
            includeYear: false,
            includeWeekDay: false,
        )
        : '';

    $href = $responseData->baseUrl . $article->uri;
    $isPublishedHtml = ArticleEditHtmlGenerator::generateArticlePublishedIconHtml($article);

    $year = $article->publishOn ? $article->publishOn->format('Y') : '???';
    if ($previousYear !== $year) {
?>
    <h2><?=$year?></h2>
<?php
  }
  $previousYear = $year;
?>
    <div class="blog-item">
      <span class="blog-item-title"><?=$isPublishedHtml?><a class="link-title" href="<?=$href?>"><?=$title?></a></span>
      <span class="blog-info"><?=$publishedOn?></span>
    </div>
<?php
}
?>
  </div>
  <div class="loading-blog-posts loading null"><img src="/img/loading.gif" class="img-svg img-svg-50" alt="<?=$responseData->getLocalValue('globalLoading')?>"></div>
<?php if (count($articles) >= $itemsPerPage) { ?>
  <a href="#" class="blog-posts-load-more" data-offset="<?=$offset?>" data-items-per-page="<?=$itemsPerPage?>"><?=$responseData->getLocalValue('globalMore')?></a>
<?php } ?>
</section>
