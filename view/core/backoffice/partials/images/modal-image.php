<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;

/** @var HtmlResponseDataAbstract $responseData */

?>
  <div class="image-modal modal-wrapper null">
    <div class="modal-inner modal-transparent">
      <a href="#" class="modal-close-button null">
        <img src="/img/svg/x-white.svg" class="img-svg img-svg-30 no-margin" alt="<?=$responseData->getLocalValue('globalClose')?>">
      </a>
      <div class="image-modal-loading justify-center">
        <img src="/img/loading.gif" class="img-svg img-svg-50" alt="<?=$responseData->getLocalValue('globalLoading')?>">
      </div>
      <div class="image-wrapper null">
        <div class="image-main">
          <div class="image-main-nav">
            <a href="#" class="image-previous-action hidden" data-direction="ASC"></a>
            <a href="#" class="image-next-action" data-direction="DESC"></a>
          </div>
        </div>
        <div class="image-info">
          <div>
            <h2 class="m-t-0 m-r-2 image-title"></h2>
            <p class="image-caption"></p>
            <p class="image-meta"></p>
            <p class="image-path"></p>
            <div class="image-appears-on"></div>
            <a href="#" class="image-delete">
              <img src="/img/svg/trash-red.svg" class="img-svg" alt="<?=$responseData->getLocalValue('globalRemove')?>">
              <?=$responseData->getLocalValue('globalRemove')?>
            </a>
          </div>
          <div class="image-next-wrapper m-t-2">
            <a href="#" class="image-previous-action hidden" data-direction="ASC"><img src="/img/svg/caret-left-white.svg" class="img-svg" alt="<?=$responseData->getLocalValue('globalNext')?>"><?=strtolower($responseData->getLocalValue('globalNext'))?></a>
            <a href="#" class="image-next-action" data-direction="DESC"><?=strtolower($responseData->getLocalValue('globalPrevious'))?><img src="/img/svg/caret-right-white.svg" class="img-svg" alt="<?=$responseData->getLocalValue('globalPrevious')?>"></a>
          </div>
        </div>
      </div>
    </div>
  </div>
