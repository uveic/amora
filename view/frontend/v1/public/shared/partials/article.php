<?php

use uve\core\model\response\HtmlResponseData;
use uve\core\util\DateUtil;

/** @var HtmlResponseData $responseData */
$article = $responseData->getFirstArticle();
$canEdit = $responseData->getSession()->isAdmin();

if ($article) {
?>
  <article class="content-medium-width m-t-2 m-b-2">
    <h1 class="article-title"><a class="black" href="<?=$this->e($responseData->getBaseUrl() . $article->getUri())?>"><?=$this->e($article->getTitle())?></a></h1>
    <p class="article-info">
      <?=$this->e(DateUtil::formatUtcDate($article->getUpdatedAt(), $responseData->getSiteLanguage(), false, true))?>
<?php if ($canEdit) { ?>
 <a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/articles/<?=$article->getId()?>"><?=strtolower($responseData->getLocalValue('globalEdit'))?></a>
<?php } ?>
    </p>
    <?=$article->getContentHtml()?>
  </article>
<?php
}
?>
