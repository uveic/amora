<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Value\QueryOrderDirection;

/** @var HtmlResponseDataAbstract $responseData */

?>
  <div class="add-image-modal modal-wrapper null">
      <div class="add-image-modal-loading justify-center">
        <img src="/img/loading.gif" class="img-svg img-svg-50" alt="<?=$responseData->getLocalValue('globalLoading')?>">
      </div>

      <div class="add-image-wrapper">
        <a href="#" class="modal-close-button">
          <img src="/img/svg/x-white.svg" class="img-svg img-svg-30 no-margin" alt="<?=$responseData->getLocalValue('globalClose')?>">
        </a>
        <div class="add-image-header">
          <h1 class="m-t-0 m-b-0">Select image to add to article</h1>
          <div>
            <input class="null" type="file" id="pexego-add-image-input" name="pexego-add-image-input" multiple="" accept="image/*">
            <label class="pexego-add-section-image pexego-add-section" for="pexego-add-image-input" style="margin: 0;">
              <img class="img-svg img-svg-30 m-r-05" src="/img/svg/image-black.svg" alt="<?=$responseData->getLocalValue('globalAddImage')?>" title="<?=$responseData->getLocalValue('globalAddImage')?>"><?=$responseData->getLocalValue('globalAddImage')?>
            </label>
          </div>
        </div>
        <div id="images-list" class="m-t-1 null"></div>
        <a href="#" class="media-load-more" data-type-id="<?=MediaType::Image->value?>" data-direction="<?=QueryOrderDirection::DESC->name?>">
          <img src="/img/svg/plus.svg" class="img-svg m-r-05 img-svg-30" title="<?=$responseData->getLocalValue('globalMore')?>" alt="<?=$responseData->getLocalValue('globalMore')?>">
          <span><?=$responseData->getLocalValue('globalMore')?></span>
        </a>
      </div>
  </div>
