<?php

use Amora\App\Module\Form\Entity\PageContent;
use Amora\App\Value\AppPageContentType;
use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Album\Model\CollectionMedia;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\Article\Value\PageContentSection;
use Amora\Core\Module\Article\Value\PageContentStatus;
use Amora\Core\Util\Helper\AlbumHtmlGenerator;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

$pageContentType = $responseData->pageContent?->type ?? $responseData->pageContentType;
$pageContentLanguage = $responseData->pageContent?->language ?? $responseData->pageContentLanguage;
$pageContentSequence = $responseData->pageContent?->sequence ?? $responseData->pageContentSequence ?? 1;

if (!$pageContentType || !$pageContentLanguage || !$pageContentSequence) {
    return;
}

$pageContentByLanguageIsoCode = [];
/** @var PageContent $item */
foreach ($responseData->pageContentAll as $item) {
    $pageContentByLanguageIsoCode[$item->language->value] = $item;
}

$title = $responseData->getLocalValue('pageContentEditTitle' . $pageContentType->name) ?: $responseData->getLocalValue('pageContentEditTitle');

$submitButtonValue = $responseData->pageContent
    ? $responseData->getLocalValue('globalUpdate')
    : $responseData->getLocalValue('globalSend');

$publicLink = AppPageContentType::buildRedirectUrl(
    type: $pageContentType,
    language: $pageContentLanguage,
);

$bulletsLink = $responseData->request->getGetParam('sequence')
    ? UrlBuilderUtil::buildBackofficeContentTypeSequenceListUrl(
        language: $responseData->siteLanguage,
        contentType: $pageContentType,
    )
    : UrlBuilderUtil::buildBackofficeContentListUrl(language: $responseData->siteLanguage);

$this->layout('base', ['responseData' => $responseData]);

$this->insert('../shared/modal-select-image', ['responseData' => $responseData]);
?>
<main>
  <div class="loading-modal null"><div class="loader"></div></div>
  <div id="feedback" class="feedback null"></div>
  <div class="page-header">
    <span class="back-js cursor-pointer"><?=CoreIcons::CARET_LEFT?></span>
    <span class="icon-one-line width-10-grow"><?=CoreIcons::ARTICLE?><span class="ellipsis"><?=$title?></span></span>
    <div class="links">
      <a href="<?=$publicLink?>"><?=CoreIcons::ARROW_SQUARE_OUT?></a>
      <a href="<?=$bulletsLink?>"><?=CoreIcons::LIST_BULLETS?></a>
    </div>
  </div>
  <form id="form-page-content" action="#" class="page-content-wrapper">
    <input type="hidden" name="languageIsoCode" value="<?=$pageContentLanguage->value?>">
    <input type="hidden" name="sequence" value="<?=$pageContentSequence?>">
<?php if (count(Core::getEnabledSiteLanguages()) > 1) { ?>
    <div class="content-flag-wrapper">
<?php
    /** @var Language $enabledLanguage */
    foreach (Core::getEnabledSiteLanguages() as $enabledLanguage) {
        echo '<span class="page-content-flag-item' . ($pageContentLanguage === $enabledLanguage ? ' flag-active' : '') . '" data-language-iso-code="' . $enabledLanguage->value . '">' . $enabledLanguage->getIconFlag() . '</span>' . PHP_EOL;

 } ?>
    </div>
<?php } ?>
    <div class="content-language-wrapper" data-page-content-type-id="<?=$pageContentType->value?>">
<?php
    /** @var Language $enabledSiteLanguage */
    foreach (Core::getEnabledSiteLanguages() as $enabledSiteLanguage) {
        $isoCode = $enabledSiteLanguage->value;
        $pageContent = $pageContentByLanguageIsoCode[$isoCode] ?? null;
?>
      <div class="content-language-item<?=$isoCode === $pageContentLanguage->value ? ' content-language-active' : ''?>" data-language-iso-code="<?=$enabledSiteLanguage->value?>">
        <input name="contentId<?=$isoCode?>" class="page-content-id" type="hidden" value="<?=$pageContent?->id?>">
        <input id="trixEditorContentHtml<?=$isoCode?>" name="pageContentContentHtml<?=$isoCode?>" class="page-content-content-html" type="hidden" value="<?=htmlspecialchars($pageContent?->contentHtml ?? '')?>">

        <div class="<?=AppPageContentType::displayContent($pageContentType, PageContentSection::Title) ? '' : ' null'?>">
          <label for="pageContentTitle<?=$isoCode?>" class="page-content-before"><?=$responseData->getLocalValue('globalTitle')?></label>
          <input id="pageContentTitle<?=$isoCode?>" name="pageContentTitle<?=$isoCode?>" type="text" maxlength="255" class="page-content-title editor-title" value="<?=$pageContent?->title?>" placeholder="<?=$responseData->getLocalValue('editorTitlePlaceholder')?>">
        </div>

        <div class="<?=AppPageContentType::displayContent($pageContentType, PageContentSection::Subtitle) ? '' : ' null'?>">
          <label for="pageContentSubtitle<?=$isoCode?>" class="page-content-before"><?=$responseData->getLocalValue('globalSubtitle')?></label>
          <input id="pageContentSubtitle<?=$isoCode?>" name="pageContentSubtitle<?=$isoCode?>" type="text" maxlength="255" class="page-content-subtitle editor-subtitle" value="<?=$pageContent?->subtitle?>" placeholder="<?=$responseData->getLocalValue('editorSubtitlePlaceholder')?>">
        </div>

        <div class="field<?=AppPageContentType::displayContent($pageContentType, PageContentSection::Excerpt) ? '' : ' null'?>">
          <label for="pageContentExcerpt<?=$isoCode?>" class="page-content-before"><?=$responseData->getLocalValue('globalExcerpt')?></label>
          <div class="control">
            <textarea id="pageContentExcerpt<?=$isoCode?>" name="pageContentExcerpt<?=$isoCode?>" maxlength="500" class="page-content-excerpt"><?=$pageContent?->excerpt?></textarea>
            <p class="help"><span class="is-danger"></span><span><?=sprintf($responseData->getLocalValue('globalMaxLength'), 500)?></span></p>
          </div>
        </div>

<?php if (AppPageContentType::displayContent($pageContentType, PageContentSection::Content)) { ?>
        <div>
          <div class="page-content-before"><?=$responseData->getLocalValue('navAdminContent')?></div>
<?php
    $this->insert('../shared/trix-editor', ['responseData' => $responseData, 'identifier' => $isoCode]);
} ?>
        </div>

        <div class="field m-l-1 m-r-1<?=AppPageContentType::displayContent($pageContentType, PageContentSection::ActionUrl) ? '' : ' null'?>">
          <label for="actionUrl<?=$isoCode?>" class="label"><?=$responseData->getLocalValue('pageContentEditAction')?>:</label>
          <div class="control">
            <input id="actionUrl<?=$isoCode?>" name="actionUrl<?=$isoCode?>" class="page-content-action-url" type="url" placeholder="" minlength="0" maxlength="255" value="<?=$pageContent?->actionUrl?>">
            <p class="help"><span class="is-danger"></span><span><?=$responseData->getLocalValue('pageContentEditActionHelp')?></span></p>
          </div>
        </div>

      </div>
<?php } ?>
    </div>

    <div class="field m-l-1 m-r-1<?=AppPageContentType::displayContent($pageContentType, PageContentSection::MainImage) ? '' : ' null'?>">
      <p class="label m-b-05"><?=$responseData->getLocalValue('editorMainImage')?>:</p>
      <div class="control">
        <div id="page-content-main-image-container">
<?php if ($responseData->pageContent?->mainImage) { ?>
          <img class="media-item" data-media-id="<?=$responseData->pageContent->mainImage->id?>" src="<?=$responseData->pageContent->mainImage->getPathWithNameMedium()?>" alt="<?=$responseData->pageContent->mainImage->buildAltText()?>">
<?php } ?>
          <div class="main-image-button-container">
            <a href="#" class="main-image-button main-image-button-red generic-media-delete-js<?=$responseData->pageContent?->mainImage ? '' : ' null'?>"><?=CoreIcons::TRASH?></a>
            <a href="#" class="main-image-button select-media-action button-media-add" data-type-id="<?=MediaType::Image->value?>" data-target-container-id="page-content-main-image-container" data-event-listener-action="handleGenericMainMediaClick">
              <?=CoreIcons::IMAGE?>
              <span><?=$responseData->pageContent?->mainImage ? $responseData->getLocalValue('globalModify') : $responseData->getLocalValue('globalSelectImage') ?></span>
            </a>
          </div>
        </div>
      </div>
    </div>

<?php if (AppPageContentType::displayContent($pageContentType, PageContentSection::Collection)) {
    $identifier = $responseData->pageContent?->collection?->id ?? StringUtil::generateRandomString(10);
?>
    <div class="field collection-container m-l-1 m-r-1 m-t-2 m-b-2" data-collection-id="<?=$responseData->pageContent?->collection?->id?>">
      <p class="label m-b-05"><?=$responseData->getLocalValue('navAdminImages')?>:</p>
      <div id="collection-item-media-<?=$identifier?>" class="collection-item-media">
<?php
        /** @var CollectionMedia $collectionMedia */
        foreach ($responseData->media as $collectionMedia) {
            echo AlbumHtmlGenerator::generateCollectionMediaHtml($collectionMedia, '        ');
        }
?>
        <a href="#" class="select-media-action button-media-add" data-type-id="<?=MediaType::Image->value?>" data-target-container-id="collection-item-media-<?=$identifier?>" data-event-listener-action="collectionAddMedia">
          <?=CoreIcons::IMAGE?>
          <span><?=$responseData->getLocalValue('globalAdd')?></span>
        </a>
      </div>
      <div class="collection-media-edit-info"><?=$responseData->getLocalValue('collectionDragAndDropToOrder')?>.</div>
    </div>
<?php } ?>

    <div class="two-columns flex-align-start border-top m-t-1 p-t-1 m-b-6 m-l-1 m-r-1">
      <div class="<?=AppPageContentType::displayContent($pageContentType, PageContentSection::Status) ? '' : 'hidden'?>">
<?=ArticleHtmlGenerator::generateDynamicPageContentStatusHtml(responseData: $responseData, status: $responseData->pageContent?->status ?? PageContentStatus::Published, indentation: '        ')?>
      </div>
      <input type="submit" class="button is-success width-auto no-margin" value="<?=$submitButtonValue?>">
    </div>
  </form>
</main>
