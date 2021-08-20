<?php

use Amora\Core\Model\Response\HtmlHomepageResponseData;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlHomepageResponseData $responseData */

if (!$responseData->getHomeArticles()) {
  return;
}

?>
<section class="home-tag">
  <h1><?=$responseData->getLocalValue('navAdminArticles')?></h1>
  <div class="home-tag-items">
<?php
/** @var Article $article */
foreach ($responseData->getHomeArticles() as $article) {
    $href = UrlBuilderUtil::getPublicArticleUrl($responseData->getSiteLanguage(), $article->getUri());
    $link = $article->getTitle()
        ? '<a class="link-title" href="' . $href . '">' . $article->getTitle() . '</a>'
        : '';
    $imgStyle = $article->getMainImage()
        ? 'style="background: url(\'' . $article->getMainImage()->getFullUrlMedium() . '\') center center / cover no-repeat;"'
        : 'style="background: url(\'/img/roof.webp\') center center / cover no-repeat;"';
        // ToDo: pick default background image when the article does not have a default image
?>
    <div class="home-tag-item">
      <a href="<?=$href?>">
        <div class="home-tag-img" <?=$imgStyle?>></div>
      </a>
      <div class="home-tag-link">
        <?=$link?>
      </div>
    </div>
<?php
}
?>
  </div>
</section>
