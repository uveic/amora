<?php

use uve\core\model\response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */

?>
    <div class="article-add-sections">
      <input class="null" type="file" id="article-add-image-input" name="article-add-image-input" multiple="" accept="image/*">
      <label class="article-add-section-image article-add-section" for="article-add-image-input">
        <img class="img-svg m-r-05" src="/img/svg/image.svg" alt="<?=$responseData->getLocalValue('globalAddImage')?>"><?=$responseData->getLocalValue('globalAddImage')?>
      </label>
      <button class="article-add-section article-add-section-text"><img class="img-svg m-r-05" src="/img/svg/article.svg" alt="<?=$responseData->getLocalValue('globalAddParagraph')?>"><?=$responseData->getLocalValue('globalAddParagraph')?></button>
      <button class="article-add-section article-add-section-video"><img class="img-svg m-r-05" src="/img/svg/youtube-logo.svg" alt="<?=$responseData->getLocalValue('globalAddVideo')?>"><?=$responseData->getLocalValue('globalAddVideo')?></button>
      <button class="article-add-section article-add-section-html"><img class="img-svg m-r-05" src="/img/svg/code.svg" alt="<?=$responseData->getLocalValue('globalAddHtml')?>"><?=$responseData->getLocalValue('globalAddHtml')?></button>
    </div>
