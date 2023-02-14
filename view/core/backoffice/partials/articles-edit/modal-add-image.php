<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Value\QueryOrderDirection;

/** @var HtmlResponseDataAbstract $responseData */

?>
  <div class="select-media-modal modal-wrapper null">
      <div class="select-media-modal-loading justify-center">
        <img src="/img/loading.gif" class="img-svg img-svg-50" alt="<?=$responseData->getLocalValue('globalLoading')?>">
      </div>

      <div class="add-image-wrapper">
        <a href="#" class="modal-close-button">
          <img src="/img/svg/x-white.svg" class="img-svg img-svg-30 no-margin" width="30" height="30" alt="<?=$responseData->getLocalValue('globalClose')?>">
        </a>
        <div class="add-image-header">
          <h3 class="m-t-0 m-b-0"><?=$responseData->getLocalValue('mediaSelectImageForArticle')?></h3>
          <div>
            <input class="null" type="file" id="select-media-action-upload" name="select-media-action-upload" multiple="" accept="image/*" data-event-listener-action="insertImageInArticle">
            <label class="input-file-label" for="select-media-action-upload">
              <img class="img-svg img-svg-30 m-r-05" src="/img/svg/image-black.svg" alt="<?=$responseData->getLocalValue('globalAddImage')?>" title="<?=$responseData->getLocalValue('globalAddImage')?>"><?=$responseData->getLocalValue('globalAddImage')?>
            </label>
          </div>
        </div>
        <div id="images-list" class="m-t-1 null"></div>
        <a href="#" class="media-load-more" data-type-id="<?=MediaType::Image->value?>" data-direction="<?=QueryOrderDirection::DESC->name?>" data-event-listener-action="insertImageInArticle">
          <img src="/img/svg/plus.svg" class="img-svg m-r-05 img-svg-30" title="<?=$responseData->getLocalValue('globalMore')?>" alt="<?=$responseData->getLocalValue('globalMore')?>">
          <span><?=$responseData->getLocalValue('globalMore')?></span>
        </a>
      </div>
  </div>
