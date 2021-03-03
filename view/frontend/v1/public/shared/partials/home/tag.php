<?php

use Amora\Core\Model\Response\HtmlHomepageResponseData;
use Amora\Core\Module\Article\Model\Article;

/** @var HtmlHomepageResponseData $responseData */

$isAdmin = $responseData->getSession() && $responseData->getSession()->isAdmin();

?>
<section class="home-tag">
  <h1><?=$responseData->getLocalValue('navAdminArticles')?></h1>
  <div class="home-tag-items">
<?php
/** @var Article $article */
foreach ($responseData->getTagArticles() as $article) {
    if (!$article->isPublished() && !$isAdmin) {
        continue;
    }

    $title = $article->getTitle()
        ? $article->getTitle()
        : $responseData->getLocalValue('globalNoTitle');
    $href = $responseData->getBaseUrl() . $article->getUri() .
        ($article->isPublished() ? '' : '?preview=true');
    $imgStyle = $article->getMainImage()
        ? 'style="background: url(\'' . $article->getMainImage()->getFullUrlMedium() . '\') center center / cover no-repeat;"'
        : 'style="background: url(\'/img/roof.webp\') center center / cover no-repeat;"';
        // ToDo: pick default background image when the article does not have a default image
    $isPublishedHtml = $article->isPublished() ? '' : '<span class="enabled-icon enabled-icon-failure m-r-05"></span>';
?>
    <div class="home-tag-item">
      <a href="<?=$href?>">
        <div class="home-tag-img" <?=$imgStyle?>></div>
      </a>
      <div class="home-tag-link">
        <?=$isPublishedHtml?><a class="link-title" href="<?=$href?>"><?=$title?></a>
      </div>
    </div>
<?php
}
?>
  </div>
</section>
