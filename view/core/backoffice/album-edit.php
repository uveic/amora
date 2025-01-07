<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Album\Value\Template;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);

$album = $responseData->album;

$pageTitle = $album
    ? ($responseData->getLocalValue('globalEdit') . ': ' . $album->titleHtml)
    : $responseData->getLocalValue('albumFormNew');
$closeLink = $album
    ? UrlBuilderUtil::buildBackofficeAlbumViewUrl($responseData->siteLanguage, $album->id)
    : UrlBuilderUtil::buildBackofficeAlbumListUrl($responseData->siteLanguage);

$albumTitle = $album ? $album->titleHtml : '';
$albumContent = $album ? $album->contentHtml : '';

$this->insert('partials/shared/modal-select-image', ['responseData' => $responseData]);

?>
  <main>
    <div id="feedback" class="feedback null"></div>
    <section class="page-header">
      <h3><?=$pageTitle?></h3>
      <div class="links"><?=CoreIcons::CLOSE?></div>
    </section>
      <div class="backoffice-wrapper">
        <form action="#" method="post" id="form-album-edit" class="form-two-columns-wrapper">
          <div>
            <input id="albumId" name="albumId" type="hidden" value="<?=$album?->id?>">
            <div class="field">
              <label for="albumTitle" class="label"><?=$responseData->getLocalValue('globalTitle')?>:</label>
              <div class="control">
                <div class="link-input-wrapper">
                  <input id="albumTitle" name="albumTitle" type="text" placeholder="" minlength="0" maxlength="255" value="<?=$albumTitle?>" required>
                </div>
                <p class="help">
                  <span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span>
                  <span></span>
                </p>
              </div>
            </div>
            <div class="field m-b-2">
              <p class="label no-margin"><?=$responseData->getLocalValue('albumFormContent')?>:</p>
              <div class="control">
                <div class="editor-content medium-editor-content album-content-html"><?=StringUtil::nl2p($albumContent)?></div>
              </div>
            </div>
            <div class="field">
              <label for="albumTemplateId" class="label">Deseño:</label>
              <div class="control">
                <select id="albumTemplateId" name="albumTemplateId">
<?php
    /** @var BackedEnum $city */
    foreach (Template::getAll() as $template) {
        $selected = $template->value === $album?->template->value;
?>
                  <option<?=($selected ? ' selected="selected"' : '')?> value="<?=$template->value?>"><?=$template->name?></option>
<?php } ?>
                </select>
              </div>
              <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
            </div>
          </div>
          <div>
            <div class="field">
              <p class="label m-b-05 m-t-0"><?=$responseData->getLocalValue('albumFormMainImageTitle')?>:</p>
              <div class="control main-image-wrapper">
                <div id="album-main-media-container" class="main-image-container main-image-container-full">
<?php if ($album?->mainMedia) { ?>
                  <img class="media-item" data-media-id="<?=$album?->mainMedia->id?>" src="<?=$album?->mainMedia->getPathWithNameMedium()?>" alt="<?=$album?->mainMedia->buildAltText()?>">
<?php } ?>
                  <div class="main-image-button-container">
                    <a href="#" class="main-image-button select-media-action" data-event-listener-action="handleGenericMainMediaClick" data-target-container-id="album-main-media-container">
                      <?=CoreIcons::IMAGE?>
                      <span><?= $album?->mainMedia ? $responseData->getLocalValue('globalModify') : $responseData->getLocalValue('editorMainImageActionTitle') ?></span>
                    </a>
                  </div>
                </div>
              </div>
              <p class="help">
                <span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span>
                <span></span>
              </p>
            </div>
          </div>
          <div class="control">
            <input type="submit" class="button is-success" value="<?=$responseData->getLocalValue('globalSend')?>">
          </div>
        </form>
      </div>
  </main>
  <script src="/js/lib/medium-editor.min.js"></script>
