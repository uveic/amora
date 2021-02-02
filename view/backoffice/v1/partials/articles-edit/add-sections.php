<?php

use uve\core\model\response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */

?>
    <div class="article-add-sections">
      <div class="article-add-section-group">
        <input class="null" type="file" id="article-add-image-input" name="article-add-image-input" multiple="" accept="image/*">
        <label class="article-add-section-image article-add-section" for="article-add-image-input">
          <img class="img-svg m-r-05" src="/img/svg/image.svg" alt="<?=$responseData->getLocalValue('globalAddImage')?>"><?=$responseData->getLocalValue('globalAddImage')?>
        </label>
        <button class="article-add-section article-add-section-video"><img class="img-svg m-r-05" src="/img/svg/youtube-logo.svg" alt="<?=$responseData->getLocalValue('globalAddVideo')?>"><?=$responseData->getLocalValue('globalAddVideo')?></button>
      </div>
      <div class="article-add-section-group">
        <button class="article-add-section article-add-section-paragraph"><img class="img-svg m-r-05" src="/img/svg/article.svg" alt="<?=$responseData->getLocalValue('globalAddParagraph')?>"><?=$responseData->getLocalValue('globalAddParagraph')?></button>
        <button class="article-add-section article-add-section-title"><img class="img-svg m-r-05" src="/img/svg/text-t.svg" alt="<?=$responseData->getLocalValue('globalAddTextTitle')?>"><?=$responseData->getLocalValue('globalAddTextTitle')?></button>
        <button class="article-add-section article-add-section-subtitle"><img class="img-svg m-r-05" src="/img/svg/text-t.svg" alt="<?=$responseData->getLocalValue('globalAddTextSubtitle')?>"><?=$responseData->getLocalValue('globalAddTextSubtitle')?></button>
      </div>
      <div class="article-add-section-group">
        <button class="article-add-section article-add-section-html"><img class="img-svg m-r-05" src="/img/svg/code.svg" alt="<?=$responseData->getLocalValue('globalAddHtml')?>"><?=$responseData->getLocalValue('globalAddHtml')?></button>
      </div>
    </div>
