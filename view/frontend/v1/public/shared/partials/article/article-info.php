<?php

use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Util\DateUtil;

/** @var HtmlResponseData $responseData */
$article = $responseData->getFirstArticle();

if (!$article) {
  return '';
}

?>
    <div class="article-info">
      <?=$this->e(DateUtil::formatUtcDate($article->getUpdatedAt(), $responseData->getSiteLanguage(), false, true))?>
    </div>
