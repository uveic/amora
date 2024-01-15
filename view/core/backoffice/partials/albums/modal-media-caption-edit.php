<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;

/** @var HtmlResponseDataAdmin $responseData */

$album = $responseData->album;

?>
<div class="album-media-caption-edit-modal-js modal-wrapper null">
  <div class="modal-inner modal-padding modal-inner-no-min-width">
    <a href="#" class="modal-close-button">
      <img src="/img/svg/x.svg" class="img-svg img-svg-30" width="30" height="30" alt="<?=$responseData->getLocalValue('globalClose')?>">
    </a>

    <div class="modal-header-container">
      <div class="modal-header-icon">
        <img class="img-svg img-svg-30" src="/img/svg/image.svg" width="30" height="30" alt="Album">
      </div>
      <div class="modal-header-content">
        <div class="modal-header-title"><?=$album->titleHtml?></div>
        <div class="modal-header-subtitle"></div>
      </div>
    </div>

    <form action="#" method="post" id="album-media-caption-edit-form-js">
      <input id="albumId" name="albumId" type="hidden" value="<?=$album->id?>">
      <input id="albumSectionMediaId" name="albumSectionMediaId" type="hidden" value="">

      <div class="form-two-columns-wrapper">
        <div class="album-media-edit-container"></div>
        <div class="editor-content medium-editor-content media-caption-html"></div>
      </div>

      <div class="control m-t-1">
        <input type="submit" class="button is-success" value="<?=$responseData->getLocalValue('globalSend')?>">
      </div>
    </form>


  </div>
</div>
