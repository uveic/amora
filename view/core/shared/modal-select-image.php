<?php

use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Value\CoreIcons;
use Amora\Core\Value\QueryOrderDirection;

/** @var HtmlResponseDataAbstract $responseData */

?>
  <div class="select-media-modal modal-wrapper null" data-media-query-qty="<?=Core::SQL_QUERY_QTY?>">
    <div class="add-image-wrapper">
      <div class="add-image-header">
        <h2 class="m-t-0 m-b-0"><?=$responseData->getLocalValue('globalSelectImage')?></h2>
        <div class="flex-end flex-align-center gap-1 flex-grow-1">
          <span class="media-original-js cursor-pointer frame"><?=CoreIcons::FRAME_CORNERS?></span>
          <span class="media-original-js cursor-pointer square null"><?=CoreIcons::SQUARE?></span>
          <span class="media-zoom-js cursor-pointer plus"><?=CoreIcons::MAGNIFYING_GLASS_PLUS?></span>
          <span class="media-zoom-js cursor-pointer minus null"><?=CoreIcons::MAGNIFYING_GLASS_MINUS?></span>
          <span class="icon-one-line media-page-wrapper">
            <label for="modalMediaSelectPage"><?=$responseData->getLocalValue('analyticsPage')?></label>
            <select name="modalMediaSelectPage" id="modalMediaSelectPage" class="media-select-page-js" data-type-id="<?=MediaType::Image->value?>" data-direction="<?=QueryOrderDirection::ASC->value?>" data-media-query-qty="<?=Core::SQL_QUERY_QTY?>"></select>
          </span>
          <a href="#" class="modal-close-link"><?=$responseData->getLocalValue('globalCancel')?></a>
          <div>
            <input class="null" type="file" id="select-media-action-upload" name="select-media-action-upload" multiple="" accept="image/png, image/jpeg, image/webp, image/svg+xml">
            <label class="input-file-label" for="select-media-action-upload"><?=CoreIcons::UPLOAD_SIMPLE?><span><?=$responseData->getLocalValue('globalUploadImage')?></span></label>
          </div>
        </div>
      </div>
      <div id="images-list" class="m-t-1 width-100">
        <div class="media-list-highlight null width-100"></div>
        <div class="media-list-page-outer null width-100"></div>
        <div class="media-list-main width-100">
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
  </div>
