<?php

use uve\core\model\response\HtmlResponseData;
use uve\core\module\article\model\Article;
use uve\core\util\DateUtil;

/** @var HtmlResponseData $responseData */

$isAdmin = $responseData->getSession() && $responseData->getSession()->isAdmin();

?>
<section class="content-medium-width m-t-2 m-b-2">
  <h1>Blog</h1>
<?php
$previousYear = null;
/** @var Article $article */
foreach ($responseData->getArticles() as $article) {
    if (!$article->isPublished() && !$isAdmin) {
        continue;
    }

  $href = $this->e($responseData->getBaseUrl() . $article->getUri()) .
      ($article->isPublished() ? '' : '?preview=true');
  $isPublished = $article->isPublished() ? '' : '<span class="enabled-icon enabled-icon-failure m-r-05"></span>';

  $year = date('Y', strtotime($article->getPublishedAt()));
  if ($previousYear !== $year) {
?>
    <h2><?=$year?></h2>
<?php
  }
  $previousYear = $year;
?>
  <div>
    <?=$isPublished?><a class="black" href="<?=$href?>"><?=$article->getTitle()?></a>
    <span class="article-info"><?=$this->e(DateUtil::formatUtcDate($article->getUpdatedAt(), $responseData->getSiteLanguage(), false, false))?></span>
  </div>
<?php
}
?>
</section>
