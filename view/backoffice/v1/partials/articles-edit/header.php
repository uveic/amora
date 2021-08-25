<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$articleTitle = ArticleEditHtmlGenerator::generateTitleHtml($responseData);
$settings = ArticleEditHtmlGenerator::generateSettingsButtonHtml($responseData);

$articleTypeId = ArticleEditHtmlGenerator::getArticleTypeId($responseData);
$closeUrl = $articleTypeId === ArticleType::PAGE
    ? UrlBuilderUtil::getBackofficeArticlesUrl($responseData->getSiteLanguage())
    : UrlBuilderUtil::getBackofficeBlogPostsUrl($responseData->getSiteLanguage());
?>
    <section class="page-header">
        <h1><?=$articleTitle?></h1>
        <div class="links">
          <?=$settings?>
          <a href="<?=$closeUrl?>" class="m-r-1"><img src="/img/svg/x.svg" class="img-svg m-t-0" alt="<?=$responseData->getLocalValue('globalClose')?>"></a>
        </div>
    </section>
