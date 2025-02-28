<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

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
      <span><?=$pageTitle?></span>
      <div class="links">
        <a href="#" class="filter-open"><?=CoreIcons::FUNNEL?></a>
        <a href="<?=UrlBuilderUtil::buildBackofficeArticleNewUrl($responseData->siteLanguage, $articleType)?>"><?=CoreIcons::ADD?></a>
        <a href="<?=UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->siteLanguage)?>"><?=CoreIcons::CLOSE?></a>
      </div>
    </section>
