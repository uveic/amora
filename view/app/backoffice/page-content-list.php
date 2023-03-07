<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\PageContentType;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */

$closeLink = UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->siteLanguage);

$this->layout('base', ['responseData' => $responseData]);
?>
<main>
  <div id="feedback" class="feedback null"></div>
  <section class="page-header">
    <h3><?=$responseData->getLocalValue('pageContentEditTitle')?></h3>
    <div class="links">
      <a href="<?=$closeLink?>"><img src="/img/svg/x.svg" class="img-svg img-svg-30" width="20" height="20" alt="Volver"></a>
    </div>
  </section>

  <div class="content-flex p-t-0">
    <div class="content-flex-block width-100">
      <div class="dashboard-cards-wrapper m-t-2">
        <a href="<?=UrlBuilderUtil::buildBackofficeContentTypeEditUrl($responseData->siteLanguage, contentType: PageContentType::Homepage, contentTypeLanguage: $responseData->siteLanguage)?>">
          <span class="text"><?=$responseData->getLocalValue('pageContentHomepage')?></span>
        </a>
        <a href="<?=UrlBuilderUtil::buildBackofficeContentTypeEditUrl($responseData->siteLanguage, contentType: PageContentType::BlogBottom, contentTypeLanguage: $responseData->siteLanguage)?>">
          <span class="text"><?=$responseData->getLocalValue('pageContentBlogBottom')?></span>
        </a>
      </div>
  </div>
</main>
