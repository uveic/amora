<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$article = $responseData->getFirstArticle();

$updatedAtContent = '<span class="article-updated-at"></span>';

if ($article) {
    $updatedAtDate = DateUtil::formatDate(
        date: DateUtil::convertStringToDateTimeImmutable($article->getUpdatedAt()),
        lang: $responseData->getSiteLanguage(),
        includeTime: true,
    );

    $updatedAtEta = DateUtil::getElapsedTimeString(
        from: DateUtil::convertStringToDateTimeImmutable($article->getUpdatedAt()),
        language: $responseData->getSiteLanguage(),
        includePrefixAndOrSuffix: true,
    );

    $updatedAtContent = $responseData->getLocalValue('globalUpdated') . ' ' .
        '<span class="article-updated-at" title="' . $updatedAtDate .
        '">' . $this->e($updatedAtEta) . '</span>.';
}

$articleStatusId = $article ? $article->getStatusId() : ArticleStatus::DRAFT;
$articleStatusName = $responseData->getLocalValue('articleStatus' . ArticleStatus::getNameForId($articleStatusId));
$isPublished = $article && $articleStatusId === ArticleStatus::PUBLISHED;

$random = StringUtil::getRandomString(5);

?>
  <div class="control-bar-wrapper m-b-3 m-t-1">
    <div class="control-bar-buttons">
      <button class="article-save-button button"><?=$article ? $responseData->getLocalValue('globalUpdate') : $responseData->getLocalValue('globalSave')?></button>
      <a class="article-disable-controls" href="#"><?=$responseData->getLocalValue('editorEnableControls')?></a>
<?php if ($article) {?>
        <a class="article-preview" href="<?=UrlBuilderUtil::getPublicArticleUrl($responseData->getSiteLanguage(), $article->getUri(), true)?>" target="_blank"><?=$responseData->getLocalValue('globalPreview')?></a>
<?php } ?>
    </div>
    <div class="control-bar-creation<?=$article ? '' : ' hidden'?>"><span><?=$updatedAtContent?></span></div>
    <div class="article-saving null">
      <img src="/img/loading.gif" class="img-svg img-svg-25" alt="Saving...">
      <span>Saving...</span>
    </div>
    <?=ArticleEditHtmlGenerator::generateStatusDropdownSelectHtml($responseData)?>
  </div>
