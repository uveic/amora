<?php

use Amora\App\Value\AppPageContentType;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\PageContentSection;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */
$pageContent = $responseData->pageContent;

$title = $responseData->getLocalValue('pageContentEditTitle' . $pageContent->type->name);
$languageIcon = count(Core::getEnabledSiteLanguages()) === 1
    ? ''
    : $pageContent->language->getIconFlag('m-l-1');
$submitButtonValue = $pageContent
    ? $responseData->getLocalValue('globalUpdate')
    : $responseData->getLocalValue('globalSend');
$closeLink = AppPageContentType::buildRedirectUrl(
    type: $pageContent?->type,
    language: $responseData->siteLanguage,
);

$this->layout('base', ['responseData' => $responseData]);

$this->insert('partials/shared/modal-select-image', ['responseData' => $responseData]);
?>
<main>
  <div id="feedback" class="feedback null"></div>
  <div class="page-header">
    <h3><?=$title . $languageIcon?></h3>
    <div class="links">
      <a href="<?=$closeLink?>"><?=CoreIcons::CLOSE?></a>
    </div>
  </div>
  <form id="form-page-content" action="#" class="page-content-wrapper">
    <input name="contentId" type="hidden" value="<?=$pageContent?->id?>">
    <input id="trixEditorContentHtml" name="pageContentContentHtml" type="hidden" value='<?=$pageContent?->contentHtml?>'>

    <label for="pageContentTitle" class="page-content-before <?=AppPageContentType::displayContent($pageContent->type, PageContentSection::Title) ? '' : ' null'?>"><?=$responseData->getLocalValue('globalTitle')?></label>
    <input id="pageContentTitle" name="pageContentTitle" type="text" maxlength="255" class="editor-title<?=AppPageContentType::displayContent($pageContent->type, PageContentSection::Title) ? '' : ' null'?>" value="<?=$pageContent?->title?>" placeholder="<?=$responseData->getLocalValue('editorTitlePlaceholder')?>" />

    <label for="pageContentSubtitle" class="page-content-before <?=AppPageContentType::displayContent($pageContent->type, PageContentSection::Subtitle) ? '' : ' null'?>"><?=$responseData->getLocalValue('globalSubtitle')?></label>
    <input id="pageContentSubtitle" name="pageContentSubtitle" type="text" maxlength="255" class="editor-subtitle<?=AppPageContentType::displayContent($pageContent->type, PageContentSection::Subtitle) ? '' : ' null'?>" value="<?=$pageContent?->subtitle?>" placeholder="<?=$responseData->getLocalValue('editorSubtitlePlaceholder')?>"/>

    <div class="page-content-before <?=AppPageContentType::displayContent($pageContent->type, PageContentSection::Content) ? '' : ' null'?>"><?=$responseData->getLocalValue('navAdminContent')?></div>
    <trix-toolbar id="trixEditorToolbar">
      <div class="trix-button-row">
        <div class="trix-button-group">
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="bold" data-trix-key="b" title="Bold"><?=CoreIcons::TEXT_B?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="italic" data-trix-key="i" title="Italic"><?=CoreIcons::TEXT_ITALIC?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="strike" title="Strikethrough"><?=CoreIcons::TEXT_STRIKETHROUGH?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="href" data-trix-action="link" data-trix-key="k" title="Link"><?=CoreIcons::LINK?></button>
        </div>
        <div class="trix-button-group">
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="heading1" title="Heading"><?=CoreIcons::TEXT_H?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="quote" title="Quote"><?=CoreIcons::QUOTES?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="code" title="Code"><?=CoreIcons::CODE?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="bullet" title="Bullets"><?=CoreIcons::LIST_BULLETS?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="number" title="Numbers"><?=CoreIcons::LIST_NUMBERS?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-action="decreaseNestingLevel" title="Decrease Level" disabled><?=CoreIcons::TEXT_INDENT?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-action="increaseNestingLevel" title="Increase Level" disabled><?=CoreIcons::TEXT_OUTDENT?></button>
        </div>
        <span class="trix-button-group-spacer"></span>
        <div class="trix-button-group" data-trix-button-group="history-tools">
          <button type="button" class="trix-button trix-button--icon" data-trix-action="undo" data-trix-key="z" title="Undo" disabled><?=CoreIcons::ARROW_ARC_LEFT?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-action="redo" data-trix-key="shift+z" title="Redo" disabled><?=CoreIcons::ARROW_ARC_RIGHT?></button>
        </div>
      </div>
      <div class="trix-dialogs" data-trix-dialogs="">
        <div class="trix-dialog trix-dialog--link" data-trix-dialog="href" data-trix-dialog-attribute="href">
          <div class="trix-dialog__link-fields">
            <input type="url" name="href" class="trix-input trix-input--dialog" placeholder="Enter a URL…" aria-label="URL" required="" data-trix-input="" disabled="disabled">
            <div class="trix-button-group">
              <input type="button" class="trix-button trix-button--dialog" value="Link" data-trix-method="setAttribute">
              <input type="button" class="trix-button trix-button--dialog" value="Unlink" data-trix-method="removeAttribute">
            </div>
          </div>
        </div>
      </div>
    </trix-toolbar>
    <trix-editor toolbar="trixEditorToolbar" input="trixEditorContentHtml" class="editor-content trix-editor-content <?=AppPageContentType::displayContent($pageContent->type, PageContentSection::Content) ? '' : ' null'?>"></trix-editor>

    <div class="field m-l-1 m-r-1<?=AppPageContentType::displayContent($pageContent->type, PageContentSection::ActionUrl) ? '' : ' null'?>">
      <label for="actionUrl" class="label"><?=$responseData->getLocalValue('pageContentEditAction')?>:</label>
      <div class="control">
        <input id="actionUrl" name="actionUrl" type="url" placeholder="" minlength="0" maxlength="255" value="<?=$pageContent?->actionUrl?>">
        <p class="help"><span class="is-danger"></span><span><?=$responseData->getLocalValue('pageContentEditActionHelp')?></span></p>
      </div>
    </div>

    <div class="field m-l-1 m-r-1 m-b-2<?=AppPageContentType::displayContent($pageContent->type, PageContentSection::MainImage) ? '' : ' null'?>">
      <p class="label m-b-05"><?=$responseData->getLocalValue('editorMainImage')?>:</p>
      <div class="control">
        <div id="page-content-main-image-container">
<?php if ($pageContent?->mainImage) { ?>
          <img class="media-item" data-media-id="<?=$pageContent->mainImage->id?>" src="<?=$pageContent->mainImage->getPathWithNameMedium()?>" alt="<?=$pageContent->mainImage->buildAltText()?>">
<?php } ?>
          <div class="main-image-button-container">
            <a href="#" class="main-image-button main-image-button-red generic-media-delete-js<?=$pageContent?->mainImage ? '' : ' null'?>"><?=CoreIcons::TRASH?></a>
            <a href="#" class="main-image-button select-media-action" data-event-listener-action="handleGenericMainMediaClick" data-target-container-id="page-content-main-image-container">
              <?=CoreIcons::IMAGE?>
              <span><?= $pageContent?->mainImage ? $responseData->getLocalValue('globalModify') : $responseData->getLocalValue('globalSelectImage') ?></span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="control m-t-2 text-right">
      <input type="submit" class="button is-success width-auto m-b-3 m-r-1" value="<?=$submitButtonValue?>">
    </div>
  </form>
</main>
