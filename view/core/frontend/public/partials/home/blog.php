<?php

use Amora\Core\Model\Response\HtmlHomepageResponseData;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;

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
  <h1>Blog <a href="<?=UrlBuilderUtil::buildPublicRssUrl()?>"><img class="img-svg img-svg-25 m-l-05" width="20" height="20" src="/img/svg/rss.svg" alt="RSS"></a></h1>
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
            lang: $responseData->siteLanguage,
            includeYear: false,
            includeWeekDay: false,
        )
        : '';

    $href = UrlBuilderUtil::buildPublicArticleUrl($article->uri, $article->language);
    $isPublishedHtml = ArticleEditHtmlGenerator::generateArticlePublishedIconHtml($article);

    $year = $article->publishOn ? $article->publishOn->format('Y') : '???';
    if ($previousYear !== $year) {
?>
    <h2 class="blog-item-year"><?=$year?></h2>
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
