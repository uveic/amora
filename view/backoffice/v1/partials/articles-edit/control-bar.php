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

$articleStatusId = $article ? $article->getStatusId() : ArticleStatus::DRAFT->value;
$articleStatusName = $responseData->getLocalValue('articleStatus' . ArticleStatus::getNameForId($articleStatusId));
$isPublished = $article && $articleStatusId === ArticleStatus::PUBLISHED->value;

$random = StringUtil::getRandomString(5);

$articleUrl = $article
    ? UrlBuilderUtil::buildPublicArticleUrl(
        uri: $article->getUri(),
        languageIsoCode: $responseData->getSiteLanguage(),
    )
    : '#';

?>
  <div class="control-bar-wrapper m-b-3 m-t-1">
    <div class="pexego-tools-amora">
      <div class="pexego-actions-amora-wrapper">
        <button class="article-save-button button"><?=$article ? $responseData->getLocalValue('globalUpdate') : $responseData->getLocalValue('globalSave')?></button>
        <div class="pexego-actions-amora">
          <a class="pexego-add-section pexego-add-section-paragraph">
            <img class="img-svg img-svg-30" src="/img/svg/article.svg" alt="<?=$responseData->getLocalValue('globalAddParagraph')?>">
          </a>
          <input class="null" type="file" id="pexego-add-image-input" name="pexego-add-image-input" multiple="" accept="image/*">
          <label class="pexego-add-section-image pexego-add-section" for="pexego-add-image-input" style="margin: 0;">
            <img class="img-svg img-svg-30" src="/img/svg/image-black.svg" alt="<?=$responseData->getLocalValue('globalAddImage')?>" title="<?=$responseData->getLocalValue('globalAddImage')?>">
          </label>
          <a class="pexego-add-section pexego-add-section-video">
            <img class="img-svg img-svg-30" src="/img/svg/youtube-logo.svg" alt="<?=$responseData->getLocalValue('globalAddVideo')?>" title="<?=$responseData->getLocalValue('globalAddVideo')?>">
          </a>
          <a href="#" class="pexego-rearrange-sections-button">
            <img class="img-svg img-svg-30" src="/img/svg/arrows-down-up.svg" title="<?=$responseData->getLocalValue('editorEnableControls')?>" alt="<?=$responseData->getLocalValue('editorEnableControls')?>">
          </a>
          <a href="<?=$articleUrl?>" class="pexego-preview<?=$article ? '' : ' null'?>">
            <img class="img-svg img-svg-30" src="/img/svg/arrow-square-out.svg" alt="<?=$responseData->getLocalValue('globalPreview')?>" title="<?=$responseData->getLocalValue('globalPreview')?>">
          </a>
        </div>
        <a href="#" class="pexego-rearrange-sections-close null">
          <img class="img-svg img-svg-30" src="/img/svg/x.svg" title="<?=$responseData->getLocalValue('globalClose')?>" alt="<?=$responseData->getLocalValue('globalClose')?>">
        </a>
      </div>
    </div>
    <div class="control-bar-creation<?=$article ? '' : ' hidden'?>"><span><?=$updatedAtContent?></span></div>
    <div class="article-saving null">
      <img src="/img/loading.gif" class="img-svg img-svg-25" alt="Saving...">
      <span>Saving...</span>
    </div>
    <?=ArticleEditHtmlGenerator::generateStatusDropdownSelectHtml($responseData)?>
  </div>
