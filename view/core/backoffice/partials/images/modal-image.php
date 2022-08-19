<?php

use Amora\Core\Model\Response\HtmlResponseDataAbstract;

/** @var HtmlResponseDataAbstract $responseData */

?>
  <div class="image-modal modal-wrapper null">
    <div class="modal-inner" style="background-color: transparent;">
      <a href="#" class="modal-close-button null">
        <img src="/img/svg/x-white.svg" class="img-svg img-svg-30 no-margin" alt="<?=$responseData->getLocalValue('globalClose')?>">
      </a>
      <div class="image-modal-loading justify-center">
        <img src="/img/loading.gif" class="img-svg img-svg-50" alt="<?=$responseData->getLocalValue('globalLoading')?>">
      </div>
      <div class="image-wrapper null">
        <div class="image-main">

        </div>
        <div class="image-info">
          <div>
            <h2 class="m-t-0 m-r-2 image-title"></h2>
            <p class="image-caption" style="word-wrap: anywhere;"></p>
            <p class="image-meta"></p>
            <a href="#" class="image-delete">
              <img src="/img/svg/trash-red.svg" class="img-svg" alt="<?=$responseData->getLocalValue('globalRemove')?>">
              <?=$responseData->getLocalValue('globalRemove')?>
            </a>
          </div>
          <div class="image-next-wrapper m-t-2">
            <a href="#" class="image-next-action" data-direction="ASC"><img src="/img/svg/caret-left-white.svg" class="img-svg" alt="<?=$responseData->getLocalValue('globalPrevious')?>"><?=strtolower($responseData->getLocalValue('globalPrevious'))?></a>
            <a href="#" class="image-next-action" data-direction="DESC"><?=strtolower($responseData->getLocalValue('globalNext'))?><img src="/img/svg/caret-right-white.svg" class="img-svg" alt="<?=$responseData->getLocalValue('globalNext')?>"></a>
          </div>
        </div>
      </div>
    </div>
  </div>
