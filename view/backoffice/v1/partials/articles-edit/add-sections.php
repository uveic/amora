<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */

?>
    <div class="pexego-add-sections">
      <div class="pexego-add-section-group">
        <input class="null" type="file" id="pexego-add-image-input" name="pexego-add-image-input" multiple="" accept="image/*">
        <label class="pexego-add-section-image pexego-add-section" for="pexego-add-image-input">
          <img class="img-svg m-r-05" src="/img/svg/image.svg" alt="<?=$responseData->getLocalValue('globalAddImage')?>"><?=$responseData->getLocalValue('globalAddImage')?>
        </label>
        <button class="pexego-add-section pexego-add-section-video"><img class="img-svg m-r-05" src="/img/svg/youtube-logo.svg" alt="<?=$responseData->getLocalValue('globalAddVideo')?>"><?=$responseData->getLocalValue('globalAddVideo')?></button>
      </div>
      <div class="pexego-add-section-group">
        <button class="pexego-add-section pexego-add-section-paragraph"><img class="img-svg m-r-05" src="/img/svg/article.svg" alt="<?=$responseData->getLocalValue('globalAddParagraph')?>"><?=$responseData->getLocalValue('globalAddParagraph')?></button>
      </div>
    </div>
