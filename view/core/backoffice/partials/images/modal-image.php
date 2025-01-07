<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAbstract $responseData */

?>
  <div class="modal-media modal-wrapper null">
    <div class="modal-inner modal-bg-dark">
      <span class="modal-close-button"><?=CoreIcons::CLOSE?></span>
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
            <div class="image-summary">
              <span class="image-number"></span>
              <a href="#" class="modal-media-close-button"><?=CoreIcons::CLOSE?></a>
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
          <div class="image-next-wrapper">
            <a href="#" class="image-previous-action hidden" data-direction="ASC"><?=CoreIcons::CARET_LEFT . strtolower($responseData->getLocalValue('globalNext'))?></a>
            <a href="#" class="image-random-action" data-direction="RAND()"><?=CoreIcons::SHUFFLE?></a>
            <a href="#" class="image-next-action" data-direction="DESC"><?=strtolower($responseData->getLocalValue('globalPrevious')) . CoreIcons::CARET_RIGHT?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
