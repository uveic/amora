<?php

use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Util\DateUtil;

/** @var HtmlResponseData $responseData */
$article = $responseData->getFirstArticle();
$canEdit = $responseData->getSession() && $responseData->getSession()->isAdmin();

if ($article) {
?>
  <article>
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
