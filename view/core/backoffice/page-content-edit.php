<?php

use Amora\App\Value\AppPageContentType;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\PageContentSection;

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
      <a href="<?=$closeLink?>"><img src="/img/svg/x.svg" class="img-svg img-svg-30" width="20" height="20" alt="Volver"></a>
    </div>
  </div>
  <form id="form-page-content" action="#" class="page-content-wrapper">
    <input name="contentId" type="hidden" value="<?=$pageContent?->id?>">

    <div class="page-content-before <?=AppPageContentType::displayContent($pageContent->type, PageContentSection::Title) ? '' : ' null'?>"><?=$responseData->getLocalValue('globalTitle')?></div>
    <h1 class="editor-title<?=$pageContent?->titleHtml ? '' : ' editor-placeholder'?><?=AppPageContentType::displayContent($pageContent->type, PageContentSection::Title) ? '' : ' null'?>" contenteditable="true"><?=$pageContent?->titleHtml ?: $responseData->getLocalValue('editorTitlePlaceholder')?></h1>

    <div class="page-content-before <?=AppPageContentType::displayContent($pageContent->type, PageContentSection::Subtitle) ? '' : ' null'?>"><?=$responseData->getLocalValue('globalSubtitle')?></div>
    <h2 class="editor-subtitle<?=$pageContent?->subtitleHtml ? '' : ' editor-placeholder'?> <?=AppPageContentType::displayContent($pageContent->type, PageContentSection::Subtitle) ? '' : ' null'?>" contenteditable="true"><?=$pageContent?->subtitleHtml ?: $responseData->getLocalValue('editorSubtitlePlaceholder')?></h2>

    <div class="page-content-before <?=AppPageContentType::displayContent($pageContent->type, PageContentSection::Content) ? '' : ' null'?>"><?=$responseData->getLocalValue('navAdminContent')?></div>
    <div class="editor-content medium-editor-content <?=AppPageContentType::displayContent($pageContent->type, PageContentSection::Content) ? '' : ' null'?>" contenteditable="true">
      <?=$pageContent?->contentHtml . PHP_EOL?>
    </div>

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
            <a href="#" class="main-image-button main-image-button-red generic-media-delete-js<?=$pageContent?->mainImage ? '' : ' null'?>">
              <img class="img-svg" src="/img/svg/trash-white.svg" alt="<?=$responseData->getLocalValue('globalRemoveImage')?>" title="<?=$responseData->getLocalValue('globalRemoveImage')?>">
            </a>
            <a href="#" class="main-image-button select-media-action" data-event-listener-action="handleGenericMainMediaClick" data-target-container-id="page-content-main-image-container">
              <img class="img-svg" src="/img/svg/image.svg" alt="<?=$responseData->getLocalValue('globalSelectImage')?>" title="<?=$responseData->getLocalValue('globalSelectImage')?>">
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
