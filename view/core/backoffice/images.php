<?php

use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\ImageSize;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;
use Amora\Core\Value\QueryOrderDirection;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);

$this->insert('partials/images/modal-image', ['responseData' => $responseData]);

$count = 0;

?>
  <div id="feedback" class="feedback null"></div>
  <main>
    <div class="page-header">
      <span class="back-js cursor-pointer"><?=CoreIcons::CARET_LEFT?></span>
      <a href="<?=UrlBuilderUtil::buildBackofficeImageListUrl($responseData->siteLanguage)?>" class="icon-one-line width-10-grow">
        <?=CoreIcons::IMAGE?>
        <span class="ellipsis"><?=$responseData->getLocalValue('navAdminImages')?></span>
      </a>
      <div class="links">
        <span class="media-original-js cursor-pointer frame"><?=CoreIcons::FRAME_CORNERS?></span>
        <span class="media-original-js cursor-pointer square null"><?=CoreIcons::SQUARE?></span>
        <span class="media-zoom-js cursor-pointer plus"><?=CoreIcons::MAGNIFYING_GLASS_PLUS?></span>
        <span class="media-zoom-js cursor-pointer minus null"><?=CoreIcons::MAGNIFYING_GLASS_MINUS?></span>
        <span class="icon-one-line media-page-wrapper">
          <label for="imagesMediaSelectPage"><?=$responseData->getLocalValue('analyticsPage')?></label>
          <select name="imagesMediaSelectPage" id="imagesMediaSelectPage" class="media-select-page-js" data-type-id="<?=MediaType::Image->value?>" data-direction="<?=QueryOrderDirection::ASC->value?>" data-media-query-qty="<?=Core::SQL_QUERY_QTY?>" data-event-listener-action="displayNextImagePopup" data-target-container-id="images-list-main">
<?php
    for ($i = $responseData->mediaLastPage ?? 0; $i >= 1 ; $i--) {
        echo '            <option value="' . $i . '"' . ($i === $responseData->mediaLastPage ? ' selected' : '') . '>' . $i . '</option>' . PHP_EOL;
    }
?>
          </select>
        </span>
        <div>
          <input class="null" type="file" id="images" name="images" multiple="" accept="image/png, image/jpeg, image/webp, image/svg+xml">
          <label for="images" class="link-add"><?=CoreIcons::ADD?></label>
        </div>
      </div>
    </div>
    <div id="images-list" class="backoffice-wrapper gap-0 width-100">
      <div class="media-list-page-outer null width-100"></div>
      <div id="images-list-main" class="media-list-main width-100">
<?php
    /** @var Media $image */
    foreach ($responseData->media as $image) {
        $count++;
?>
        <figure class="image-container">
          <?=$image->asHtml(size: ImageSize::Small, lazyLoading: $count > 10) . PHP_EOL?>
        </figure>
<?php } ?>
        <a href="#" class="media-load-more media-load-more-js<?=$count >= Core::SQL_QUERY_QTY ? '' : ' null'?>" data-type-id="<?=MediaType::Image->value?>" data-direction="<?=QueryOrderDirection::DESC->name?>" data-media-query-qty="<?=Core::SQL_QUERY_QTY?>" data-event-listener-action="displayNextImagePopup">
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
  </main>
