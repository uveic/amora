<?php

use uve\core\model\response\HtmlResponseDataAuthorised;

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
        <button class="pexego-add-section pexego-add-section-title"><img class="img-svg m-r-05" src="/img/svg/text-t.svg" alt="<?=$responseData->getLocalValue('globalAddTextTitle')?>"><?=$responseData->getLocalValue('globalAddTextTitle')?></button>
        <button class="pexego-add-section pexego-add-section-subtitle"><img class="img-svg m-r-05" src="/img/svg/text-t.svg" alt="<?=$responseData->getLocalValue('globalAddTextSubtitle')?>"><?=$responseData->getLocalValue('globalAddTextSubtitle')?></button>
      </div>
      <div class="pexego-add-section-group">
        <button class="pexego-add-section pexego-add-section-html"><img class="img-svg m-r-05" src="/img/svg/code.svg" alt="<?=$responseData->getLocalValue('globalAddHtml')?>"><?=$responseData->getLocalValue('globalAddHtml')?></button>
      </div>
    </div>
