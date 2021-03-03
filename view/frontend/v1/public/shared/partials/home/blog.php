<?php

use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Util\DateUtil;

/** @var HtmlResponseData $responseData */

$isAdmin = $responseData->getSession() && $responseData->getSession()->isAdmin();

?>
<section class="home-blog">
  <h1>Blog</h1>
<?php
$previousYear = null;
/** @var Article $article */
foreach ($responseData->getArticles() as $article) {
    if (!$article->isPublished() && !$isAdmin) {
        continue;
    }

    $title = $article->getTitle()
        ? $article->getTitle()
        : $responseData->getLocalValue('globalNoTitle');
    $publishedOn = $article->getPublishOn()
        ? DateUtil::formatUtcDate(
            $article->getPublishOn(),
            $responseData->getSiteLanguage(),
            false,
            false,
            $article->getUser()->getTimezone(),
            false
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
  <p class="blog-item">
    <?=$isPublishedHtml?><a class="link-title" href="<?=$href?>"><?=$title?></a>
    <span class="blog-info"><?=$publishedOn?></span>
  </p>
<?php
}
?>
</section>
