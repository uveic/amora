<?php

use Amora\Core\Model\Response\HtmlHomepageResponseData;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Util\DateUtil;

/** @var HtmlHomepageResponseData $responseData */

$isAdmin = $responseData->getSession() && $responseData->getSession()->isAdmin();

if (!$responseData->getBlogArticles()) {
  return;
}

?>
<section class="home-blog">
  <h1>Blog</h1>
<?php
$previousYear = null;
/** @var Article $article */
foreach ($responseData->getBlogArticles() as $article) {
    if (!$article->isPublished() && !$isAdmin) {
        continue;
    }

    $title = $article->getTitle() ?: $responseData->getLocalValue('globalNoTitle');
    $publishedOn = $article->getPublishOn()
        ? DateUtil::formatDate(
            date: DateUtil::convertStringToDateTimeImmutable($article->getPublishOn()),
            lang: $responseData->getSiteLanguage(),
            includeYear: false,
            includeWeekDay: false,
        )
        : '';

    $href = $this->e($responseData->getBaseUrl() . $article->getUri()) .
        ($article->isPublished() ? '' : '?preview=true');
    $isPublishedHtml = $article->isPublished() ? '' : '<span class="enabled-icon enabled-icon-failure m-r-05"></span>';

    $year = $article->getPublishOn() ? date('Y', strtotime($article->getPublishOn())) : '???';
    if ($previousYear !== $year) {
?>
    <h2><?=$year?></h2>
<?php
  }
  $previousYear = $year;
?>
  <div class="blog-item">
    <?=$isPublishedHtml?>
    <a class="link-title" href="<?=$href?>"><?=$title?></a>
    <span class="blog-info"><?=$publishedOn?></span>
  </div>
<?php
}
?>
  <div class="loading-blog-posts loading null"><img src="/img/loading.gif" class="img-svg img-svg-50" alt="<?=$responseData->getLocalValue('globalLoading')?>"></div>
  <a href="#" class="blog-posts-load-more">Máis</a>
</section>
