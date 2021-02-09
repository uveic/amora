<?php

use uve\core\model\response\HtmlResponseData;
use uve\core\util\DateUtil;

/** @var HtmlResponseData $responseData */
$article = $responseData->getFirstArticle();
$canEdit = $responseData->getSession() && $responseData->getSession()->isAdmin();

if ($article) {
?>
  <article class="content-medium-width m-t-2 m-b-2">
    <?=$article->getContentHtml()?>
    <div class="article-info">
      <?=$this->e(DateUtil::formatUtcDate($article->getUpdatedAt(), $responseData->getSiteLanguage(), false, true))?>
<?php if ($canEdit) { ?>
 <a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/articles/<?=$article->getId()?>"><?=strtolower($responseData->getLocalValue('globalEdit'))?></a>
<?php } ?>
    </div>
  </article>
<?php
}
?>
