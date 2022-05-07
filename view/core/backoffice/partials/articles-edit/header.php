<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$pageEditTitle = ArticleEditHtmlGenerator::generateTitleHtml($responseData);
$settings = ArticleEditHtmlGenerator::generateSettingsButtonHtml($responseData);

$articleType = ArticleEditHtmlGenerator::getArticleType($responseData);
$closeUrl = match($articleType) {
    ArticleType::Page => UrlBuilderUtil::buildBackofficeArticlesUrl($responseData->siteLanguage, ArticleType::Page),
    ArticleType::Blog => UrlBuilderUtil::buildBackofficeArticlesUrl($responseData->siteLanguage, ArticleType::Blog),
    ArticleType::PartialContentHomepage, ArticleType::PartialContentBlogBottom => UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->siteLanguage),
};

$updatedAtContent = $responseData->getLocalValue('globalUpdated')
    . ' '
    . '<span class="article-updated-at"></span>';

$article = $responseData->getFirstArticle();

if ($article) {
    $updatedAtDate = DateUtil::formatDate(
        date: $article->updatedAt,
        lang: $responseData->siteLanguage,
        includeTime: true,
    );

    $updatedAtEta = DateUtil::getElapsedTimeString(
        language: $responseData->siteLanguage,
        from: $article->updatedAt,
        includePrefixAndOrSuffix: true,
    );

    $updatedAtContent = $responseData->getLocalValue('globalUpdated') . ' ' .
        '<span class="article-updated-at" title="' . $updatedAtDate .
        '">' . $this->e($updatedAtEta) . '</span>.';
}

?>
    <section class="page-header">
        <h3><?=$pageEditTitle?></h3>
        <div class="header-info">
          <div class="article-saving null">
            <img src="/img/loading.gif" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalSaving')?>">
            <span><?=$responseData->getLocalValue('globalSaving')?></span>
          </div>
          <div class="control-bar-creation<?=$article ? '' : ' hidden'?>"><span><?=$updatedAtContent?></span></div>
          <div class="links">
            <?=$settings?>
            <a href="<?=$closeUrl?>"><img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalClose')?>"></a>
          </div>
        </div>
    </section>
