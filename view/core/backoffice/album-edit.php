<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);

$album = $responseData->album;

$pageTitle = $album
    ? ($responseData->getLocalValue('globalEdit') . ': ' . $album->title)
    : $responseData->getLocalValue('albumFormNew');
$closeLink = $album
    ? UrlBuilderUtil::buildBackofficeAlbumViewUrl($responseData->siteLanguage, $album->id)
    : UrlBuilderUtil::buildBackofficeAlbumListUrl($responseData->siteLanguage);

$albumTitle = $album ? $album->title : '';
$albumContent = $album ? $album->contentHtml : '';

// $this->insert('partials/album-date/modal-select-main-image', ['responseData' => $responseData]);

?>
  <main>
    <div id="feedback" class="feedback null"></div>
    <section class="page-header">
      <h3><?=$pageTitle?></h3>
      <div class="links">
        <a href="<?=$closeLink?>"><img src="/img/svg/x.svg" class="img-svg img-svg-30" width="20" height="20" alt="<?=$responseData->getLocalValue('globalClose')?>"></a>
      </div>
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
            <div class="field">
              <p class="label no-margin"><?=$responseData->getLocalValue('albumFormContent')?>:</p>
              <div class="control">
                <div class="editor-content medium-editor-content m-b-3"><?=StringUtil::nl2p($albumContent)?></div>
              </div>
            </div>
          </div>
          <div>
            <div class="field">
              <p class="label m-b-05 m-t-0"><?=$responseData->getLocalValue('albumFormMainImageTitle')?>:</p>
              <div class="control main-image-wrapper">
                <div class="main-image-container main-image-container-full">
<?php if ($album?->mainMedia) { ?>
                  <img class="event-main-image" data-media-id="<?=$album?->mainMedia->id?>" src="<?=$album?->mainMedia->getPathWithNameMedium()?>" alt="<?=$album?->mainMedia->buildAltText()?>">
<?php } ?>
                  <div class="main-image-button-container">
                    <a href="#" class="main-image-button main-image-button-red album-main-image-delete-action<?=$album?->mainMedia ? '' : ' null'?>">
                      <img class="img-svg" src="/img/svg/trash-white.svg" alt="<?=$responseData->getLocalValue('globalRemoveImage')?>" title="<?=$responseData->getLocalValue('globalRemoveImage')?>">
                    </a>
                    <a href="#" class="main-image-button event-main-image-action" data-event-listener-action="eventSelectMainImage">
                      <img class="img-svg" src="/img/svg/image.svg" alt="<?=$responseData->getLocalValue('globalAddImage')?>" title="<?=$responseData->getLocalValue('globalAddImage')?>">
                      <span><?= $album?->mainMedia ? $responseData->getLocalValue('globalModify') : $responseData->getLocalValue('editorMainImageActionTitle') ?></span>
                    </a>
                  </div>
                </div>
              </div>
          </div>
        </form>
      </div>
  </main>
  <script src="/js/lib/medium-editor.min.js"></script>
