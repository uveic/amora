<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Model\Media;

/** @var HtmlResponseDataAuthorised $responseData */

$this->layout('base', ['responseData' => $responseData]);

$this->insert('partials/images/modal-image', ['responseData' => $responseData]);

$count = 0;

?>
  <div id="feedback" class="feedback null"></div>
  <main>
    <div class="content-images">
      <div class="field m-t-0 m-b-0">
        <div id="upload-images">
          <div id="upload-images-info">
            <h1><?=$responseData->getLocalValue('navAdminImages')?></h1>
          </div>
          <div id="upload-images-control">
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
    foreach ($responseData->images as $image) {
        $count++;
        $lazyLoading = $count > 10 ? ' loading="lazy"' : '';
        $alt = $image->caption ?? $image->filenameOriginal;
?>
        <a href="#" class="image-item" data-image-id="<?=$image->id?>">
          <img src="<?=$image->getUriWithNameMedium()?>" title="<?=$alt?>" alt="<?=$alt?>"<?=$lazyLoading?>>
        </a>
<?php } ?>
      </div>
    </div>
  </main>
