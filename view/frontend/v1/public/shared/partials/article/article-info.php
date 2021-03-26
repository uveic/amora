<?php

use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Util\DateUtil;

/** @var HtmlResponseData $responseData */
$article = $responseData->getFirstArticle();

if (!$article) {
  return '';
}

$updatedAtDate = DateUtil::formatUtcDate(
    stringDate: $article->getUpdatedAt(),
    lang: $responseData->getSiteLanguage(),
    includeWeekDay: false,
    includeTime: true
);

?>
    <div class="article-info">
      <?=$this->e($updatedAtDate)?>
    </div>
