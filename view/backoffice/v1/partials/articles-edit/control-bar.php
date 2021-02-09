<?php

use uve\core\model\response\HtmlResponseDataAuthorised;
use uve\core\module\article\value\ArticleStatus;
use uve\core\util\DateUtil;
use uve\core\util\StringUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$article = $responseData->getFirstArticle();

$updatedAtContent = $responseData->getLocalValue('globalUpdated') . ' ' . ($article
    ? '<span class="article-updated-at" title="' .
    $this->e(DateUtil::formatUtcDate($article->getUpdatedAt(), $responseData->getSiteLanguage(), true, true, $responseData->getTimezone())) .
    '">' . $this->e(DateUtil::getElapsedTimeString($article->getUpdatedAt(), $responseData->getSiteLanguage(), false, true)) . '</span>.'
    : '<span class="article-updated-at" title=""></span>');

$articleStatusId = $article ? $article->getStatusId() : ArticleStatus::DRAFT;
$articleStatusName = $responseData->getLocalValue('articleStatus' . ArticleStatus::getNameForId($articleStatusId));
$isPublished = $article ? $articleStatusId === ArticleStatus::PUBLISHED : false;

$random = StringUtil::getRandomString(5);

?>
  <div class="control-bar-wrapper m-b-3 m-t-1">
    <div class="article-control-bar-buttons">
      <input style="width: revert;" type="submit" class="article-save button m-r-1" value="<?=$article ? $responseData->getLocalValue('globalUpdate') : $responseData->getLocalValue('globalSave')?>">
<?php if ($article) {?>
        <a class="article-preview" href="<?=$responseData->getBaseUrlWithLanguage()?><?=$article->getUri()?>?preview=true" target="_blank"><?=$responseData->getLocalValue('globalPreview')?></a>
<?php } ?>
    </div>
    <div class="control-bar-creation<?=$article ? '' : ' hidden'?>"><span><?=$updatedAtContent?></span></div>
    <div class="article-saving null">
      <img src="/img/loading.gif" class="" alt="Saving...">
      <span>Saving...</span>
    </div>
    <input type="checkbox" id="dropdown-menu-<?=$random?>" class="dropdown-menu">
    <div class="dropdown-container">
      <ul>
<?php foreach ($responseData->getArticleStatuses() as $status) { ?>
        <li><a data-checked="<?=$status['id'] === $articleStatusId ? '1' : '0'?>" data-article-status-id="<?=$this->e($status['id'])?>" class="dropdown-menu-option article-status-option <?=$status['id'] === ArticleStatus::PUBLISHED ? 'feedback-success' : 'background-light-color' ?>" href="#"><?=$responseData->getLocalValue('articleStatus' . $status['name'])?></a></li>
<?php } ?>
      </ul>
      <label for="dropdown-menu-<?=$random?>" class="dropdown-menu-label <?=$isPublished ? 'feedback-success' : 'background-light-color' ?>">
        <span><?=$articleStatusName?></span>
        <img class="img-svg no-margin" width="20" height="20" src="/img/svg/caret-down.svg" alt="Change">
      </label>
    </div>
  </div>
