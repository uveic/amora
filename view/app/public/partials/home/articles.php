<?php

use Amora\App\Entity\AppHtmlHomepageResponseData;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Util\UrlBuilderUtil;

/** @var AppHtmlHomepageResponseData $responseData */

if ($responseData->request->session && $responseData->request->session->isAdmin()) {
  return;
}

if (!$responseData->homeArticles) {
  return;
}

?>
  <section class="home-tag">
    <h1><?=$responseData->getLocalValue('navAdminArticles')?></h1>
    <div class="home-tag-items">
<?php
/** @var Article $article */
foreach ($responseData->homeArticles as $article) {
    $href = UrlBuilderUtil::buildPublicArticlePath(
        path: $article->path,
        language: $responseData->siteLanguage,
    );
    $link = $article->title
        ? '<a class="link-title" href="' . $href . '">' . $article->title . '</a>'
        : '';
    $mainImage = $article->mainImage
        ? '<img src="' . $article->mainImage->fullUrlMedium . '" alt="' . $article->mainImage->buildAltText() . '">'
        : '';
?>
      <div class="home-tag-item">
        <a href="<?=$href?>">
          <div class="home-tag-img">
            <?=$mainImage?>
          </div>
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
