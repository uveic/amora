<?php

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */
$pageContent = $responseData->pageContent;

$title = $responseData->getLocalValue('pageContentEditTitle' . $pageContent->type->name);
$languageIcon = count(Core::getAllLanguages()) === 1
    ? ''
    : Language::getIconFlag($pageContent->language, 'm-l-1');
$submitButtonValue = $pageContent
    ? $responseData->getLocalValue('globalUpdate')
    : $responseData->getLocalValue('globalSend');
$closeLink = UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage);

$this->layout('base', ['responseData' => $responseData]);

$this->insert('partials/articles-edit/modal-select-main-image', ['responseData' => $responseData]);
?>
<main>
  <div id="feedback" class="feedback null"></div>
  <section class="page-header">
    <h3><?=$title . $languageIcon?></h3>
    <div class="links">
      <a href="<?=$closeLink?>"><img src="/img/svg/x.svg" class="img-svg img-svg-30" width="20" height="20" alt="Volver"></a>
    </div>
  </section>
  <form id="form-page-content" action="#" class="page-content-wrapper">
    <input name="contentId" type="hidden" value="<?=$pageContent?->id?>">

    <div class="page-content-before"><?=$responseData->getLocalValue('globalTitle')?></div>
    <h1 class="editor-title page-content-title <?=$pageContent?->title ? '' : ' editor-placeholder'?>" contenteditable="true"><?=$pageContent?->title ?: $responseData->getLocalValue('editorTitlePlaceholder')?></h1>

    <div class="page-content-before"><?=$responseData->getLocalValue('globalSubtitle')?></div>
    <h2 class="editor-subtitle page-content-subtitle <?=$pageContent?->subtitle ? '' : ' editor-placeholder'?>" contenteditable="true"><?=$pageContent?->subtitle ?: $responseData->getLocalValue('editorSubtitlePlaceholder')?></h2>

    <div class="page-content-before"><?=$responseData->getLocalValue('navAdminContent')?></div>
    <div class="editor-content medium-editor-content m-t-05" contenteditable="true">
      <?=$pageContent?->html . PHP_EOL?>
    </div>

    <div class="field">
      <p class="label m-b-05"><?=$responseData->getLocalValue('editorMainImage')?>:</p>
      <div class="control article-main-image-wrapper">
        <div class="article-main-image-container article-main-image-container-full">
<?php if ($pageContent?->mainImage) { ?>
          <img class="article-main-image" data-media-id="<?=$pageContent->mainImage->id?>" src="<?=$pageContent->mainImage->getPathWithNameMedium()?>" alt="<?=$pageContent->mainImage->caption?>">
<?php } ?>
          <a href="#" class="article-main-image-button article-main-image-button-absolute select-media-action" data-event-listener-action="selectMainImage">
            <img class="img-svg img-svg-30" src="/img/svg/image.svg" alt="<?=$responseData->getLocalValue('globalSelectImage')?>" title="<?=$responseData->getLocalValue('globalSelectImage')?>">
            <span><?= $pageContent?->mainImage ? $responseData->getLocalValue('globalModify') : $responseData->getLocalValue('globalSelectImage') ?></span>
          </a>
        </div>
      </div>
    </div>

    <div class="control m-t-2">
      <input type="submit" class="button is-success m-b-3" value="<?=$submitButtonValue?>">
    </div>
  </form>
</main>
