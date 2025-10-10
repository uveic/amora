<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAbstract $responseData */

?>
  <div class="modal-media modal-wrapper null">
    <span class="modal-media-close-wrapper flex-end gap-1">
      <a href="#" class="image-info-action"><?=CoreIcons::INFO?></a>
      <a href="#" class="image-random-action" data-direction="RAND()"><?=CoreIcons::SHUFFLE?></a>
      <span class="modal-media-close"><?=CoreIcons::CLOSE?></span>
    </span>
    <div class="modal-inner modal-bg-transparent">
      <div class="image-wrapper null">
        <div class="loader-media"></div>
        <div class="image-main">
          <div class="image-main-nav">
            <a href="#" class="image-previous-action hidden" data-direction="ASC"></a>
            <a href="#" class="image-next-action" data-direction="DESC"></a>
          </div>
        </div>
        <div class="image-info">
          <div>
            <div class="image-summary flex-start space-between gap-1">
              <a href="#" class="image-info-close-button"><?=CoreIcons::CARET_LEFT?></a>
              <span class="image-number"></span>
            </div>
            <div class="image-info-data null">
              <div class="image-meta"></div>
              <div class="image-path"></div>
              <div class="image-caption"></div>
              <div class="image-appears-on"></div>
              <a href="#" class="image-delete">
                  <?=CoreIcons::TRASH?>
                  <?=$responseData->getLocalValue('globalRemove')?>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="image-next-wrapper">
      <a href="#" class="image-previous-action hidden" data-direction="ASC"><?=CoreIcons::CARET_LEFT?></a>
      <a href="#" class="image-next-action" data-direction="DESC"><?=CoreIcons::CARET_RIGHT?></a>
    </div>
  </div>
