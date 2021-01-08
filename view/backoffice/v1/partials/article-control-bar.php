<?php

use uve\core\model\response\HtmlResponseDataAuthorised;
use uve\core\module\article\value\ArticleStatus;
use uve\core\util\DateUtil;
use uve\core\util\StringUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$article = $responseData->getFirstArticle();

$updatedAtContent = $article
    ? 'Updated <span class="articleUpdatedAt" title="' .
    $this->e(DateUtil::formatUtcDate($article->getUpdatedAt(), $responseData->getSiteLanguage(), true, true, $responseData->getTimezone())) .
    '">' . $this->e(DateUtil::getElapsedTimeString($article->getUpdatedAt(), $responseData->getSiteLanguage(), false, true)) . '</span>.'
    : '';

$createdAtContent = $article
    ? 'Created <span title="' .
    $this->e(DateUtil::formatUtcDate($article->getCreatedAt(), $responseData->getSiteLanguage(), true, true, $responseData->getTimezone())) .
    '">' . $this->e(DateUtil::getElapsedTimeString($article->getCreatedAt(), $responseData->getSiteLanguage(), false, true)) . '</span>.'
    : '';

$articleStatusId = $article ? $article->getStatusId() : ArticleStatus::DRAFT;
$articleStatusName = ArticleStatus::getNameForId($articleStatusId);
$isPublished = $article ? $articleStatusId === ArticleStatus::PUBLISHED : false;

$random = StringUtil::getRandomString(5);

?>
  <div class="form-control-bar-header m-b-3">
    <input style="width: revert;" type="submit" class="button m-r-1" value="<?=$article ? 'Update' : 'Save'?>">
    <input style="width: revert;" type="submit" class="button" data-close="1" value="<?=$article ? 'Update & Close' : 'Save & Close'?>">
    <div style="text-align: right"><?=$updatedAtContent?><br><?=$createdAtContent?></div>
    <input type="checkbox" id="dropdown-menu-<?=$random?>" class="dropdown-menu">
    <div class="dropdown-container">
      <ul>
<?php foreach ($responseData->getArticleStatuses() as $status) { ?>
        <li><a data-checked="<?=$status['id'] === $articleStatusId ? '1' : '0'?>" data-article-status-id="<?=$this->e($status['id'])?>" class="dropdown-menu-option article-status-option <?=$status['id'] === ArticleStatus::PUBLISHED ? 'feedback-success' : 'background-light-color' ?>" href="#"><?=$this->e($status['name'])?></a></li>
<?php } ?>
      </ul>
      <label for="dropdown-menu-<?=$random?>" class="dropdown-menu-label <?=$isPublished ? 'feedback-success' : 'background-light-color' ?>">
        <span><?=$articleStatusName?></span>
        <img class="img-svg no-margin" width="20" height="20" src="/img/assets/caret-down.svg" alt="Change">
      </label>
    </div>
  </div>
