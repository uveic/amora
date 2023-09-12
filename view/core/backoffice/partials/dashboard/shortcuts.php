<?php

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\Article\Value\PageContentType;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAbstract $responseData */

$homepageLinks = [];
$partialContentLinks = [];

/** @var Language $language */
foreach (Core::getAllLanguages() as $language) {
    $homepageLinks[] = '<a class="m-l-1" href="' . UrlBuilderUtil::buildBackofficeContentTypeEditUrl($responseData->siteLanguage, PageContentType::Homepage, $language) . '">' . Language::getIconFlag($language, 'img-svg-25') . '</a>';
    $partialContentLinks[] = '<a class="m-l-1" href="' . UrlBuilderUtil::buildBackofficeContentTypeEditUrl($responseData->siteLanguage, PageContentType::BlogBottom, $language) . '">' . Language::getIconFlag($language, 'img-svg-25') . '</a>';
}
?>
        <div class="m-t-2">
          <h2><?=$responseData->getLocalValue('dashboardShortcuts')?></h2>
          <div class="dashboard-cards-wrapper">
            <div>
              <span><?=$responseData->getLocalValue('pageContentEditTitleHomepage')?>:</span>
              <span><?=implode('', $homepageLinks)?></span>
            </div>
            <div>
              <span><?=$responseData->getLocalValue('pageContentEditTitleBlogBottom')?>:</span>
              <span><?=implode('', $partialContentLinks)?></span>
            </div>
            <a href="<?=UrlBuilderUtil::buildBackofficeNewArticleUrl($responseData->siteLanguage, ArticleType::Blog)?>">
              <img class="img-svg img-svg-40" width="40" height="40" src="/img/svg/article-medium.svg" alt="<?=$responseData->getLocalValue('navAdminBlogPosts')?>">
              <?=$responseData->getLocalValue('dashboardNewBlogPost')?>
            </a>
          </div>
        </div>
