<?php

use Amora\App\Module\Form\Entity\PageContent;
use Amora\App\Value\AppPageContentType;
use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Album\Model\CollectionMedia;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\Article\Value\PageContentSection;
use Amora\Core\Util\Helper\AlbumHtmlGenerator;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */
$mainPageContent = $responseData->pageContent;
if (!$mainPageContent) {
    return;
}

$pageContentByLanguageIsoCode = [];
/** @var PageContent $item */
foreach ($responseData->pageContentAll as $item) {
    $pageContentByLanguageIsoCode[$item->language->value] = $item;
}

$title = $responseData->getLocalValue('pageContentEditTitle' . $mainPageContent->type->name);
$submitButtonValue = $mainPageContent
    ? $responseData->getLocalValue('globalUpdate')
    : $responseData->getLocalValue('globalSend');
$publicLink = AppPageContentType::buildRedirectUrl(
    type: $mainPageContent->type,
    language: $mainPageContent->language,
);

$this->layout('base', ['responseData' => $responseData]);

$this->insert('partials/shared/modal-select-image', ['responseData' => $responseData]);
?>
<main>
  <div class="loading-modal null"><div class="loader"></div></div>
  <div id="feedback" class="feedback null"></div>
  <div class="page-header">
    <span class="back-js cursor-pointer"><?=CoreIcons::CARET_LEFT?></span>
    <span class="icon-one-line width-10-grow"><?=CoreIcons::ARTICLE?><span class="ellipsis"><?=$title?></span></span>
    <div class="links">
      <a href="<?=$publicLink?>"><?=CoreIcons::ARROW_SQUARE_OUT?></a>
      <a href="<?=UrlBuilderUtil::buildBackofficeContentListUrl(language: $responseData->siteLanguage)?>"><?=CoreIcons::LIST_BULLETS?></a>
    </div>
  </div>
  <form id="form-page-content" action="#" class="page-content-wrapper">
    <input type="hidden" name="languageIsoCode" value="<?=$mainPageContent->language->value?>">
<?php if (count(Core::getEnabledSiteLanguages()) > 1) { ?>
    <div class="content-flag-wrapper">
<?php
    /** @var Language $enabledLanguage */
    foreach (Core::getEnabledSiteLanguages() as $enabledLanguage) {
        echo '<span class="page-content-flag-item' . ($mainPageContent->language === $enabledLanguage ? ' flag-active' : '') . '" data-language-iso-code="' . $enabledLanguage->value . '">' . $enabledLanguage->getIconFlag() . '</span>' . PHP_EOL;

 } ?>
    </div>
<?php } ?>
    <div class="content-language-wrapper" data-page-content-type-id="<?=$mainPageContent->type->value?>">
<?php
    /** @var Language $enabledSiteLanguage */
    foreach (Core::getEnabledSiteLanguages() as $enabledSiteLanguage) {
        $isoCode = $enabledSiteLanguage->value;
        $pageContent = $pageContentByLanguageIsoCode[$isoCode] ?? null;
?>
      <div class="content-language-item<?=$isoCode === $mainPageContent->language->value ? ' content-language-active' : ''?>" data-language-iso-code="<?=$enabledSiteLanguage->value?>">
        <input name="contentId<?=$isoCode?>" class="page-content-id" type="hidden" value="<?=$pageContent?->id?>">
        <input id="trixEditorContentHtml<?=$isoCode?>" name="pageContentContentHtml<?=$isoCode?>" class="page-content-content-html" type="hidden" value="<?=htmlspecialchars($pageContent?->contentHtml ?? '')?>">

        <label for="pageContentTitle<?=$isoCode?>" class="page-content-before <?=AppPageContentType::displayContent($mainPageContent->type, PageContentSection::Title) ? '' : ' null'?>"><?=$responseData->getLocalValue('globalTitle')?></label>
        <input id="pageContentTitle<?=$isoCode?>" name="pageContentTitle<?=$isoCode?>" type="text" maxlength="255" class="page-content-title editor-title<?=AppPageContentType::displayContent($mainPageContent->type, PageContentSection::Title) ? '' : ' null'?>" value="<?=$pageContent?->title?>" placeholder="<?=$responseData->getLocalValue('editorTitlePlaceholder')?>" />

        <label for="pageContentSubtitle<?=$isoCode?>" class="page-content-before <?=AppPageContentType::displayContent($mainPageContent->type, PageContentSection::Subtitle) ? '' : ' null'?>"><?=$responseData->getLocalValue('globalSubtitle')?></label>
        <input id="pageContentSubtitle<?=$isoCode?>" name="pageContentSubtitle<?=$isoCode?>" type="text" maxlength="255" class="page-content-subtitle editor-subtitle<?=AppPageContentType::displayContent($mainPageContent->type, PageContentSection::Subtitle) ? '' : ' null'?>" value="<?=$pageContent?->subtitle?>" placeholder="<?=$responseData->getLocalValue('editorSubtitlePlaceholder')?>"/>

<?php if (AppPageContentType::displayContent($mainPageContent->type, PageContentSection::Content)) { ?>
        <div class="page-content-before <?=AppPageContentType::displayContent($mainPageContent->type, PageContentSection::Content) ? '' : ' null'?>"><?=$responseData->getLocalValue('navAdminContent')?></div>
<?php
    $this->insert('../shared/trix-editor', ['responseData' => $responseData, 'identifier' => $isoCode]);
} ?>
        <div class="field m-l-1 m-r-1 m-t-2 m-b-2<?=AppPageContentType::displayContent($mainPageContent->type, PageContentSection::ActionUrl) ? '' : ' null'?>">
          <label for="actionUrl<?=$isoCode?>" class="label"><?=$responseData->getLocalValue('pageContentEditAction')?>:</label>
          <div class="control">
            <input id="actionUrl<?=$isoCode?>" name="actionUrl<?=$isoCode?>" class="page-content-action-url" type="url" placeholder="" minlength="0" maxlength="255" value="<?=$pageContent?->actionUrl?>">
            <p class="help"><span class="is-danger"></span><span><?=$responseData->getLocalValue('pageContentEditActionHelp')?></span></p>
          </div>
        </div>

      </div>
<?php } ?>
    </div>

    <div class="field m-l-1 m-r-1 m-b-2<?=AppPageContentType::displayContent($mainPageContent?->type, PageContentSection::MainImage) ? '' : ' null'?>">
      <p class="label m-b-05"><?=$responseData->getLocalValue('editorMainImage')?>:</p>
      <div class="control">
        <div id="page-content-main-image-container">
<?php if ($mainPageContent?->mainImage) { ?>
          <img class="media-item" data-media-id="<?=$mainPageContent->mainImage->id?>" src="<?=$mainPageContent->mainImage->getPathWithNameMedium()?>" alt="<?=$mainPageContent->mainImage->buildAltText()?>">
<?php } ?>
          <div class="main-image-button-container">
            <a href="#" class="main-image-button main-image-button-red generic-media-delete-js<?=$mainPageContent?->mainImage ? '' : ' null'?>"><?=CoreIcons::TRASH?></a>
            <a href="#" class="main-image-button select-media-action button-media-add" data-type-id="<?=MediaType::Image->value?>" data-target-container-id="page-content-main-image-container" data-event-listener-action="handleGenericMainMediaClick">
              <?=CoreIcons::IMAGE?>
              <span><?= $mainPageContent?->mainImage ? $responseData->getLocalValue('globalModify') : $responseData->getLocalValue('globalSelectImage') ?></span>
            </a>
          </div>
        </div>
      </div>
    </div>

<?php if (AppPageContentType::displayContent($mainPageContent->type, PageContentSection::Collection)) {
    $identifier = $mainPageContent->collection?->id ?? StringUtil::generateRandomString(10);
?>
    <div class="field m-l-1 m-r-1 m-t-2 m-b-2">
      <p class="label m-b-05"><?=$responseData->getLocalValue('navAdminImages')?>:</p>
      <div id="collection-item-media-<?=$identifier?>" class="collection-item-media" data-collection-id="<?=$mainPageContent->collection?->id?>">
<?php
        /** @var CollectionMedia $collectionMedia */
        foreach ($responseData->media as $collectionMedia) {
            echo AlbumHtmlGenerator::generateCollectionMediaHtml($collectionMedia, '        ');
        }
?>
        <a href="#" class="button select-media-action button-media-add" data-type-id="<?=MediaType::Image->value?>" data-target-container-id="collection-item-media-<?=$identifier?>" data-event-listener-action="collectionAddMedia">
          <?=CoreIcons::IMAGE?>
          <span><?=$responseData->getLocalValue('globalAdd')?></span>
        </a>
      </div>
      <div class="collection-media-edit-info"><?=$responseData->getLocalValue('collectionDragAndDropToOrder')?>.</div>
    </div>
<?php } ?>

    <div class="control m-t-2 text-right">
      <input type="submit" class="button is-success width-auto m-b-3 m-r-1" value="<?=$submitButtonValue?>">
    </div>
  </form>
</main>
