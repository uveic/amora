<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$articleTypeIdGetParam = $responseData->request->getGetParam('type');
$articleType = $articleTypeIdGetParam
    ? (ArticleType::tryFrom($articleTypeIdGetParam)
        ? ArticleType::from($articleTypeIdGetParam)
        : ArticleType::Page
    ) : ArticleType::Page;

$pageTitle = match ($articleType) {
    ArticleType::Blog => $responseData->getLocalValue('navAdminBlogPosts'),
    ArticleType::Page => $responseData->getLocalValue('navAdminArticles'),
    ArticleType::PartialContentHomepage, ArticleType::PartialContentBlogBottom
        => $responseData->getLocalValue('navAdminPartialContent'),
};
?>
    <section class="page-header">
      <h3><?=$pageTitle?></h3>
      <div class="links">
        <a href="#" id="filter-open"><img src="/img/svg/funnel.svg" class="img-svg img-svg-25 m-r-1" alt="Funnel"></a>
        <a href="<?=UrlBuilderUtil::buildBackofficeNewArticleUrl($responseData->siteLanguage, $articleType)?>" class="button is-link admin-menu-button"><?=$responseData->getLocalValue('globalNew')?></a>
      </div>
    </section>
