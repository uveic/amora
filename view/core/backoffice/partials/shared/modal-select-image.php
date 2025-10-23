<?php

use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Module\Article\Service\MediaService;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Value\CoreIcons;
use Amora\Core\Value\QueryOrderDirection;

/** @var HtmlResponseDataAbstract $responseData */

?>
  <div class="select-media-modal modal-wrapper null" data-media-query-qty="<?=Core::SQL_QUERY_QTY?>">
    <div class="add-image-wrapper">
      <div class="add-image-header">
        <h2 class="m-t-0 m-b-0"><?=$responseData->getLocalValue('globalSelectImage')?></h2>
        <div class="flex-start space-between gap-1">
          <a href="#" class="modal-close-link"><?=$responseData->getLocalValue('globalCancel')?></a>
          <div>
            <input class="null" type="file" id="select-media-action-upload" name="select-media-action-upload" multiple="" accept="image/png, image/jpeg, image/webp">
            <label class="input-file-label" for="select-media-action-upload"><?=CoreIcons::IMAGE?><span><?=$responseData->getLocalValue('globalAddImage')?></span></label>
          </div>
        </div>
      </div>
      <div class="media-list-highlight null"></div>
      <div id="images-list" class="null m-t-1">
        <a href="#" class="media-load-more media-load-more-js null" data-type-id="<?=MediaType::Image->value?>" data-direction="<?=QueryOrderDirection::DESC->name?>" data-media-query-qty="<?=Core::SQL_QUERY_QTY?>">
          <span><?=$responseData->getLocalValue('globalMore')?></span>
        </a>
        <div class="image-container-shadow"></div>
        <div class="image-container-shadow"></div>
        <div class="image-container-shadow"></div>
        <div class="image-container-shadow"></div>
        <div class="image-container-shadow"></div>
        <div class="image-container-shadow"></div>
        <div class="image-container-shadow"></div>
        <div class="image-container-shadow"></div>
        <div class="image-container-shadow"></div>
        <div class="image-container-shadow"></div>
        <div class="image-container-shadow"></div>
        <div class="image-container-shadow"></div>
        <div class="image-container-shadow"></div>
        <div class="image-container-shadow"></div>
        <div class="image-container-shadow"></div>
        <div class="image-container-shadow"></div>
      </div>
    </div>
  </div>
