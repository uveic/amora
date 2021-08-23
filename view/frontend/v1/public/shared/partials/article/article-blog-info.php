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
    includeTime: true,
    includeMonthYearSeparator: true,
);

?>
    <h1><?=$article->getTitle()?></h1>
    <p class="article-blog-info"><?=$this->e($updatedAtDate)?></p>
