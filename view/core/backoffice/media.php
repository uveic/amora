<?php

use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Service\MediaService;
use Amora\Core\Value\CoreIcons;
use Amora\Core\Value\QueryOrderDirection;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);

$displayLoadMore = count($responseData->media) >= 50;

?>
  <div id="feedback" class="feedback null"></div>
  <main>
    <div class="page-header">
      <span class="back-js cursor-pointer"><?=CoreIcons::CARET_LEFT?></span>
      <span class="icon-one-line width-10-grow"><?=CoreIcons::FILES?><span class="ellipsis"><?=$responseData->getLocalValue('navAdminMedia')?></span></span>
      <div class="links">
        <div class="upload-media-control">
          <input class="null" type="file" id="media" name="media" multiple="" accept="*">
          <label for="media" class="cursor-pointer"><?=CoreIcons::ADD?></label>
        </div>
      </div>
    </div>

    <div class="backoffice-wrapper">
      <div id="media-container">
<?php
    /** @var Media $media */
    foreach ($responseData->media as $media) {
?>
        <div class="file-container">
          <?=$media->asHtml()?>
        </div>
<?php } ?>
        <a href="#" class="media-load-more media-load-more-js<?=$displayLoadMore ? '' : ' null'?>" data-type-id="" data-direction="<?=QueryOrderDirection::DESC->name?>" data-media-query-qty="<?=Core::SQL_QUERY_QTY?>" data-event-listener-action="displayNextImagePopup">
          <span><?=$responseData->getLocalValue('globalMore')?></span>
        </a>
      </div>
    </div>
  </main>
