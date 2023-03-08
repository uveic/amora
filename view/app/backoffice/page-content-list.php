<?php

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\PageContentType;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */

$closeLink = UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->siteLanguage);

$homepageLinks = [];
$partialContentLinks = [];

/** @var Language $language */
foreach (Core::getAllLanguages() as $language) {
    $homepageLinks[] = '<a class="m-l-1" href="' . UrlBuilderUtil::buildBackofficeContentTypeEditUrl($responseData->siteLanguage, PageContentType::Homepage, $language) . '">' . Language::getIconFlag($language, 'img-svg-25') . '</a>';
    $partialContentLinks[] = '<a class="m-l-1" href="' . UrlBuilderUtil::buildBackofficeContentTypeEditUrl($responseData->siteLanguage, PageContentType::BlogBottom, $language) . '">' . Language::getIconFlag($language, 'img-svg-25') . '</a>';
}

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
<?php if (count($homepageLinks) > 1) { ?>
        <div>
          <span><?=$responseData->getLocalValue('pageContentEditTitleHomepage')?>:</span>
          <span><?=implode('', $homepageLinks)?></span>
        </div>
<?php } else { ?>
        <a href="<?=UrlBuilderUtil::buildBackofficeContentTypeEditUrl($responseData->siteLanguage, contentType: PageContentType::Homepage, contentTypeLanguage: $responseData->siteLanguage)?>">
          <span class="text"><?=$responseData->getLocalValue('pageContentEditTitleHomepage')?></span>
        </a>
<?php } ?>
<?php if (count($partialContentLinks) > 1) { ?>
        <div>
          <span><?=$responseData->getLocalValue('pageContentEditTitleBlogBottom')?>:</span>
          <span><?=implode('', $partialContentLinks)?></span>
        </div>
<?php } else { ?>
        <a href="<?=UrlBuilderUtil::buildBackofficeContentTypeEditUrl($responseData->siteLanguage, contentType: PageContentType::BlogBottom, contentTypeLanguage: $responseData->siteLanguage)?>">
          <span class="text"><?=$responseData->getLocalValue('pageContentEditTitleBlogBottom')?></span>
        </a>
<?php } ?>
      </div>
  </div>
</main>
