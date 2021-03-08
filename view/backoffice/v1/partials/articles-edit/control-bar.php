<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;
use Amora\Core\Util\StringUtil;

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
    <div class="control-bar-buttons">
      <button class="article-save-button button m-r-1"><?=$article ? $responseData->getLocalValue('globalUpdate') : $responseData->getLocalValue('globalSave')?></button>
<?php if ($article) {?>
        <a class="article-preview" href="<?=$responseData->getBaseUrlWithLanguage()?><?=$article->getUri()?>?preview=true" target="_blank"><?=$responseData->getLocalValue('globalPreview')?></a>
<?php } ?>
    </div>
    <div class="control-bar-creation<?=$article ? '' : ' hidden'?>"><span><?=$updatedAtContent?></span></div>
    <div class="article-saving null">
      <img src="/img/loading.gif" class="img-svg img-svg-25" alt="Saving...">
      <span>Saving...</span>
    </div>
    <?=ArticleEditHtmlGenerator::generateStatusDropdownSelectHtml($responseData)?>
  </div>
