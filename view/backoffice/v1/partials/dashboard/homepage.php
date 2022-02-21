<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$articleEditUrl = $responseData->getFirstArticle()
    ? UrlBuilderUtil::buildBackofficeArticleUrl(
        $responseData->getSiteLanguage(),
        $responseData->getFirstArticle()->id,
    )
    : UrlBuilderUtil::buildBackofficeNewArticleUrl(
        languageIsoCode: $responseData->getSiteLanguage(),
        articleTypeId: ArticleType::Homepage->value,
    );
?>
        <div class="content-flex-block width-45-percent">
          <h2><?=$responseData->getLocalValue('dashboardShortcuts')?></h2>
          <p><img class="img-svg m-r-05" width="20" height="20" src="/img/svg/pencil.svg" alt="<?=$responseData->getLocalValue('articleEditHomepageTitle')?>"><a href="<?=$articleEditUrl?>"><?=$responseData->getLocalValue('articleEditHomepageTitle')?></a></p>
          <p><img class="img-svg m-r-05" width="20" height="20" src="/img/svg/article-medium.svg" alt="' . $localisationUtil->getValue('navAdminBlogPosts') . '">
            <a href="<?=UrlBuilderUtil::buildBackofficeNewBlogPostUrl($responseData->getSiteLanguage())?>"><?=$responseData->getLocalValue('dashboardNewBlogPost')?></a>
          </p>
        </div>
