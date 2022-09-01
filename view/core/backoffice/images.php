<?php

use Amora\Core\Entity\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Value\QueryOrderDirection;

/** @var HtmlResponseDataAuthorised $responseData */

$this->layout('base', ['responseData' => $responseData]);

$this->insert('partials/images/modal-image', ['responseData' => $responseData]);

$count = 0;

?>
  <div id="feedback" class="feedback null"></div>
  <main>
    <div class="content-images">
      <div class="field m-t-0 m-b-0">
        <div id="upload-media">
          <div id="upload-media-info">
            <h1><?=$responseData->getLocalValue('navAdminImages')?></h1>
          </div>
          <div id="upload-media-control">
            <input class="null" type="file" id="images" name="images" multiple="" accept="image/*">
            <label for="images" class="input-file-label">
              <img class="img-svg img-svg-25 m-r-05" width="20" height="20" src="/img/svg/image-black.svg" alt="<?=$responseData->getLocalValue('navAdminImages')?>'">
              <span><?=$responseData->getLocalValue('globalUploadImage')?></span>
            </label>
          </div>
        </div>
      </div>
      <div id="images-list">
<?php
    /** @var Media $image */
    foreach ($responseData->files as $image) {
        $count++;
        $lazyLoading = $count > 10 ? ' loading="lazy"' : '';
        $alt = $image->caption ?? $image->filenameOriginal;
?>
        <a href="#" class="image-item" data-image-id="<?=$image->id?>">
          <img src="<?=$image->getUriWithNameMedium()?>" title="<?=$alt?>" alt="<?=$alt?>"<?=$lazyLoading?>>
        </a>
<?php } ?>
      </div>
<?php if ($count) { ?>
      <a href="#" class="media-load-more" data-type-id="<?=MediaType::Image->value?>" data-direction="<?=QueryOrderDirection::DESC->name?>">
        <img src="/img/svg/plus.svg" class="img-svg m-r-05 img-svg-30" title="<?=$responseData->getLocalValue('globalMore')?>" alt="<?=$responseData->getLocalValue('globalMore')?>">
        <span><?=$responseData->getLocalValue('globalMore')?></span>
      </a>
<?php } ?>
    </div>
  </main>
