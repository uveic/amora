<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;

/** @var HtmlResponseDataAbstract $responseData */

?>
  <div class="add-image-modal modal-wrapper null">
      <a href="#" class="modal-close-button">
        <img src="/img/svg/x-white.svg" class="img-svg img-svg-30 no-margin" alt="<?=$responseData->getLocalValue('globalClose')?>">
      </a>
      <div class="add-image-modal-loading justify-center">
        <img src="/img/loading.gif" class="img-svg img-svg-50" alt="<?=$responseData->getLocalValue('globalLoading')?>">
      </div>
      <div class="add-image-container null">
        <input class="null" type="file" id="pexego-add-image-input" name="pexego-add-image-input" multiple="" accept="image/*">
        <label class="pexego-add-section-image pexego-add-section" for="pexego-add-image-input" style="margin: 0;">
          <img class="img-svg img-svg-30 m-r-05" src="/img/svg/image-black.svg" alt="<?=$responseData->getLocalValue('globalAddImage')?>" title="<?=$responseData->getLocalValue('globalAddImage')?>"><?=$responseData->getLocalValue('globalAddImage')?>
        </label>
      </div>
  </div>
