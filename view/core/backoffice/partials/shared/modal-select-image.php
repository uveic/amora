<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Value\CoreIcons;
use Amora\Core\Value\QueryOrderDirection;

/** @var HtmlResponseDataAbstract $responseData */

?>
  <div class="select-media-modal modal-wrapper null">
    <div class="select-media-modal-loading justify-center">
      <img src="/img/loading.gif" class="img-svg img-svg-50" alt="<?=$responseData->getLocalValue('globalLoading')?>">
    </div>

    <div class="add-image-wrapper">
      <span class="modal-close-button"><?=CoreIcons::CLOSE?></span>
      <div class="add-image-header">
        <h2 class="m-t-0 m-b-0"><?=$responseData->getLocalValue('globalSelectImage')?></h2>
        <div>
          <input class="null" type="file" id="select-media-action-upload" name="select-media-action-upload" multiple="" accept="image/png, image/jpeg, image/webp">
          <label class="input-file-label" for="select-media-action-upload">
            <img class="img-svg" src="/img/svg/image.svg" alt="<?=$responseData->getLocalValue('globalAddImage')?>" title="<?=$responseData->getLocalValue('globalAddImage')?>">
            <span><?=$responseData->getLocalValue('globalAddImage')?></span>
          </label>
        </div>
      </div>
      <div id="images-list" class="null"></div>
      <a href="#" class="media-load-more media-load-more-js null" data-type-id="<?=MediaType::Image->value?>" data-direction="<?=QueryOrderDirection::DESC->name?>">
        <span><?=$responseData->getLocalValue('globalMore')?></span>
      </a>
    </div>
  </div>
