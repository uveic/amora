<?php

use Amora\Core\Model\Response\HtmlHomepageResponseData;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlHomepageResponseData $responseData */

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
    $href = UrlBuilderUtil::buildPublicArticleUrl(
        uri: $article->uri,
        language: $responseData->siteLanguage,
    );
    $link = $article->title
        ? '<a class="link-title" href="' . $href . '">' . $article->title . '</a>'
        : '';
    $imgStyle = $article->mainImage
        ? 'style="background: url(\'' . $article->mainImage->fullUrlMedium . '\') center center / cover no-repeat;"'
        : 'style="background: url(\'/img/roof.webp\') center center / cover no-repeat;"';
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
