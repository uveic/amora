<?php

use uve\core\model\response\HtmlResponseDataAuthorised;
use uve\core\module\article\value\ArticleStatus;
use uve\core\util\DateUtil;
use uve\core\util\StringUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$article = $responseData->getFirstArticle();

$updatedAtContent = 'Updated ' . ($article
    ? '<span class="article-updated-at" title="' .
    $this->e(DateUtil::formatUtcDate($article->getUpdatedAt(), $responseData->getSiteLanguage(), true, true, $responseData->getTimezone())) .
    '">' . $this->e(DateUtil::getElapsedTimeString($article->getUpdatedAt(), $responseData->getSiteLanguage(), false, true)) . '</span>.'
    : '<span class="article-updated-at" title=""></span>');

$createdAtContent = 'Created ' . ($article
    ? '<span title="' .
    $this->e(DateUtil::formatUtcDate($article->getCreatedAt(), $responseData->getSiteLanguage(), true, true, $responseData->getTimezone())) .
    '">' . $this->e(DateUtil::getElapsedTimeString($article->getCreatedAt(), $responseData->getSiteLanguage(), false, true)) . '</span>.'
    : '<span class="article-created-at" title=""></span>');

$articleStatusId = $article ? $article->getStatusId() : ArticleStatus::DRAFT;
$articleStatusName = ArticleStatus::getNameForId($articleStatusId);
$isPublished = $article ? $articleStatusId === ArticleStatus::PUBLISHED : false;

$random = StringUtil::getRandomString(5);

?>
  <div class="form-control-bar-header m-b-3">
    <input style="width: revert;" type="submit" class="article-save button m-r-1" value="<?=$article ? 'Update' : 'Save'?>">
    <input style="width: revert;" type="submit" class="article-save-close button" data-close="1" value="<?=$article ? 'Update & Close' : 'Save & Close'?>">
    <div class="article-creation<?=$article ? '' : ' hidden'?>" style="text-align: right"><?=$updatedAtContent?><br><?=$createdAtContent?></div>
    <div class="article-saving null">
      <img src="/img/loading.gif" class="" alt="Saving...">
      <span>Saving...</span>
    </div>
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