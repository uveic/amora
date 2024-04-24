<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\ImageSize;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Value\QueryOrderDirection;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);

$this->insert('partials/images/modal-image', ['responseData' => $responseData]);

$count = 0;

?>
  <div id="feedback" class="feedback null"></div>
  <main>
    <div class="content-images">
      <div id="upload-media">
        <h1><?=$responseData->getLocalValue('navAdminImages')?></h1>
        <div class="upload-media-control">
          <input class="null" type="file" id="images" name="images" multiple="" accept="image/png, image/jpeg, image/webp">
          <label for="images" class="input-file-label">
            <img class="img-svg" width="20" height="20" src="/img/svg/image.svg" alt="<?=$responseData->getLocalValue('navAdminImages')?>">
            <span><?=$responseData->getLocalValue('globalUploadImage')?></span>
          </label>
        </div>
      </div>
      <div id="images-list">
<?php
    /** @var Media $image */
    foreach ($responseData->files as $image) {
        $count++;
?>
        <figure class="image-container">
          <?=$image->asHtml(size: ImageSize::Small, lazyLoading: $count > 10) . PHP_EOL?>
        </figure>
<?php } ?>
      </div>
<?php if ($count) { ?>
      <a href="#" class="media-load-more media-load-more-js" data-type-id="<?=MediaType::Image->value?>" data-direction="<?=QueryOrderDirection::DESC->name?>" data-event-listener-action="displayNextImagePopup">
        <span><?=$responseData->getLocalValue('globalMore')?></span>
      </a>
<?php } ?>
    </div>
  </main>
