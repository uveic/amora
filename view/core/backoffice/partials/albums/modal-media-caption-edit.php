<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

$album = $responseData->album;

?>
<div class="album-media-caption-edit-modal-js modal-wrapper null">
  <div class="modal-inner modal-padding modal-inner-no-min-width">
    <span class="modal-close-button"><?=CoreIcons::CLOSE?></span>

    <div class="modal-header-container">
      <div class="modal-header-icon">
        <?=CoreIcons::IMAGE?>
      </div>
      <div class="modal-header-content">
        <div class="modal-header-title"><?=$album->titleHtml?></div>
        <div class="modal-header-subtitle"></div>
      </div>
    </div>

    <form action="#" method="post" id="album-media-caption-edit-form-js">
      <input id="albumId" name="albumId" type="hidden" value="<?=$album->id?>">
      <input id="collectionMediaId" name="collectionMediaId" type="hidden" value="">

      <div class="form-two-columns-wrapper">
        <div class="album-media-edit-container"></div>
        <div class="media-caption-html" contenteditable="true"></div>
      </div>

      <div class="control m-t-1">
        <input type="submit" class="button is-success" value="<?=$responseData->getLocalValue('globalSend')?>">
      </div>
    </form>


  </div>
</div>
