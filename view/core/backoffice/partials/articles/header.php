<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */

$articleTypeIdGetParam = $responseData->request->getGetParam('atId');
$articleType = !empty($articleTypeIdGetParam) && ArticleType::tryFrom($articleTypeIdGetParam)
    ? ArticleType::from($articleTypeIdGetParam)
    : ArticleType::Page;

$pageTitle = match ($articleType) {
    ArticleType::Blog => $responseData->getLocalValue('navAdminBlogPosts'),
    ArticleType::Page => $responseData->getLocalValue('navAdminArticles'),
};
?>
    <section class="page-header">
      <h3><?=$pageTitle?></h3>
      <div class="links">
        <a href="#" class="filter-open"><img src="/img/svg/funnel.svg" class="img-svg img-svg-25" alt="Funnel"></a>
        <a href="<?=UrlBuilderUtil::buildBackofficeNewArticleUrl($responseData->siteLanguage, $articleType)?>" class="button is-link header-button-new"><?=$responseData->getLocalValue('globalNew')?></a>
      </div>
    </section>
