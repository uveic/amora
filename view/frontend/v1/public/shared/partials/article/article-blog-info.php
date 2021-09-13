<?php

use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Util\DateUtil;

/** @var HtmlResponseData $responseData */
$article = $responseData->getFirstArticle();

if (!$article) {
  return '';
}

$publishedOnDate = DateUtil::formatDate(
    date: DateUtil::convertStringToDateTimeImmutable(
        $article->getPublishOn() ?? $article->getUpdatedAt()
    ),
    lang: $responseData->getSiteLanguage(),
    includeWeekDay: false,
    includeTime: true,
    includeMonthYearSeparator: true,
);

?>
    <h1><?=$article->getTitle()?></h1>
    <p class="article-blog-info"><?=$this->e($publishedOnDate)?></p>
