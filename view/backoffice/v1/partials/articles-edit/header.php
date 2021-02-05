<?php

use uve\core\model\response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */
$article = $responseData->getFirstArticle();

?>
    <div class="form-header m-t-1 m-l-1 m-r-1">
        <h1><?=($article ? $responseData->getLocalValue('globalEdit') : $responseData->getLocalValue('globalNew')) . ' ' . $responseData->getLocalValue('globalArticle')?></h1>
        <div class="links">
          <a href="#" class="article-settings m-r-1"><img src="/img/svg/gear.svg" class="img-svg m-t-0" alt="<?=$responseData->getLocalValue('globalSettings')?>"></a>
          <a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/articles" class="m-r-1"><img src="/img/svg/x.svg" class="img-svg m-t-0" alt="<?=$responseData->getLocalValue('globalClose')?>"></a>
        </div>
    </div>
