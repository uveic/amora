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
            <label for="images" class="input-file-label"><span class="m-r-05"><svg xmlns="http://www.w3.org/2000/svg" width="192" height="192" fill="#212529" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><rect x="32" y="48" width="192" height="160" rx="8" stroke-width="16" stroke="#212529" stroke-linecap="round" stroke-linejoin="round" fill="none"></rect><path d="M32,167.99982l50.343-50.343a8,8,0,0,1,11.31371,0l44.68629,44.6863a8,8,0,0,0,11.31371,0l20.68629-20.6863a8,8,0,0,1,11.31371,0L223.99982,184" fill="none" stroke="#212529" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path><circle cx="156" cy="100" r="12"></circle></svg></span><?=$responseData->getLocalValue('globalUploadImage')?></label>
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
