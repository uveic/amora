<?php

use Amora\App\Value\Language;
use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$homepageLinks = [];
$partialContentLinks = [];

/** @var \BackedEnum $item */
foreach (Language::getAll() as $item) {
    $homepageLinks[] = '<a class="m-l-1" href="' . UrlBuilderUtil::buildBackofficeNewArticleUrl($item, ArticleType::PartialContentHomepage) . '">' . Language::getIconFlag($item) . '</a>';
    $partialContentLinks[] = '<a class="m-l-1" href="' . UrlBuilderUtil::buildBackofficeNewArticleUrl($item, ArticleType::PartialContentBlogBottom) . '">' . Language::getIconFlag($item) . '</a>';
}
?>
        <div class="content-flex-block width-45-percent">
          <h2><?=$responseData->getLocalValue('dashboardShortcuts')?></h2>
          <p>
            <img class="img-svg m-r-05" width="20" height="20" src="/img/svg/pencil.svg" alt="<?=$responseData->getLocalValue('articleEditHomepageTitle')?>">
            <?=$responseData->getLocalValue('articleEditHomepageTitle')?>:<?=implode('', $homepageLinks)?>
          </p>
          <p>
            <img class="img-svg m-r-05" width="20" height="20" src="/img/svg/pencil.svg" alt="<?=$responseData->getLocalValue('articleEditBottomBlogPostTitle')?>">
              <?=$responseData->getLocalValue('articleEditBottomBlogPostTitle')?>:<?=implode('', $partialContentLinks)?>
          </p>
          <p>
            <img class="img-svg m-r-05" width="20" height="20" src="/img/svg/article-medium.svg" alt="' . $localisationUtil->getValue('navAdminBlogPosts') . '">
            <a href="<?=UrlBuilderUtil::buildBackofficeNewArticleUrl($responseData->siteLanguage, ArticleType::Blog)?>"><?=$responseData->getLocalValue('dashboardNewBlogPost')?></a>
          </p>
        </div>
