<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;

/** @var HtmlResponseDataAdmin $responseData */

$album = $responseData->album;

?>
<div class="album-add-section-modal-js modal-wrapper null">
  <div class="modal-loading justify-center">
    <img src="/img/loading.gif" class="img-svg img-svg-50" alt="<?=$responseData->getLocalValue('globalLoading')?>">
  </div>

  <div class="modal-inner modal-padding null">
    <a href="#" class="modal-close-button">
      <img src="/img/svg/x.svg" class="img-svg img-svg-30" width="30" height="30" alt="<?=$responseData->getLocalValue('globalClose')?>">
    </a>

    <div class="modal-header-container">
      <div class="modal-header-icon">
        <img class="img-svg img-svg-30" src="/img/svg/image.svg" width="30" height="30" alt="Album">
      </div>
      <div class="modal-header-content">
        <div class="modal-header-title"><?=$album->titleHtml?></div>
      </div>
    </div>

    <form action="#" method="post" id="album-add-section-form-js">
      <input id="albumId" name="albumId" type="hidden" value="<?=$album->id?>">

      <div class="field">
        <label for="albumSectionTitle" class="label"><?=$responseData->getLocalValue('globalTitle')?>:</label>
        <div class="control">
          <div class="link-input-wrapper">
            <input id="albumSectionTitle" name="albumSectionTitle" type="text" placeholder="" minlength="0" maxlength="255" value="" required>
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
          <div class="editor-content medium-editor-content album-content-html"></div>
        </div>
      </div>

      <div class="control">
        <input type="submit" class="button is-success" value="<?=$responseData->getLocalValue('globalSend')?>">
      </div>
    </form>


  </div>
</div>
