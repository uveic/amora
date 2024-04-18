<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Model\Tag;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */
$article = $responseData->article;

$publishOnDate = '';
$publishOnTime = '';
if ($article && $article->publishOn) {
    $publishOnDate = $article->publishOn->format('Y-m-d');
    $publishOnTime = $article->publishOn->format('H:i');
}

$tags = [];
$createdAtContent = '';
if ($article) {
    $tags = $article->tags;
    $createdAtContent = $responseData->getLocalValue('globalCreated') . ' ' .
        DateUtil::getElapsedTimeString(
            language: $responseData->siteLanguage,
            from: $article->createdAt,
            includePrefixAndOrSuffix: true,
        ) . ' ('
        . DateUtil::formatDate(
            date: $article->createdAt,
            lang: $responseData->siteLanguage,
            includeTime: true,
        ) . ')'
        . ' '
        . $responseData->getLocalValue('globalBy') . ' '
        . $article->user->name . '.';
}

?>
<div class="side-nav-wrapper">
  <a href="#" class="select-media-action" data-type-id="<?=MediaType::Image->value?>" data-event-listener-action="insertImageInArticle">
    <img class="img-svg img-svg-30" src="/img/svg/image.svg" alt="<?=$responseData->getLocalValue('globalAddImage')?>" title="<?=$responseData->getLocalValue('globalAddImage')?>">
  </a>
<?=ArticleHtmlGenerator::generateArticleLanguageDropdownSelectHtml($responseData)?>
<?=ArticleHtmlGenerator::generateArticleStatusDropdownSelectHtml($responseData)?>
  <div>
    <label for="tags" class="label"><?=$responseData->getLocalValue('globalTags')?>:</label>
    <div id="tags-selected" class="search-results-selected<?=$tags ? '' : ' null'?>">
<?php
    /** @var Tag $tag */
    foreach ($tags as $tag) {
?>
      <span class="result-selected" data-tag-id="<?=$tag->id?>" data-tag-name="<?=$tag->name?>"><?=$tag->name?><img class="tag-result-selected-delete img-svg m-l-05" title="<?=$responseData->getLocalValue('globalRemove')?>" alt="<?=$responseData->getLocalValue('globalRemove')?>" src="/img/svg/x.svg"></span>
<?php } ?>
    </div>
    <div class="control">
      <input class="input search-input" id="tags" name="tags" type="text" placeholder="<?=$responseData->getLocalValue('globalTags')?>" value="">
      <div class="search-wrapper">
        <div id="search-results-tags" class="search-results null">
          <a href="#" class="search-results-close"><img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="Close"></a>
        </div>
      </div>
    </div>
  </div>
  <div>
    <div class="label"><?=$responseData->getLocalValue('formArticlePath')?>:</div>
    <div class="article-edit-path"><?=UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage) . '/'?>
      <div class="article-path-value" contenteditable="true"><?=$this->e($article ? $article->path : ''); ?></div>
    </div>
    <div class="article-edit-previous-path-container null">
      <div class="label m-t-1"><?=$responseData->getLocalValue('formArticlePreviousPaths')?>:</div>
      <div class="article-edit-previous-path-content">
        <img src="/img/loading.gif" class="img-svg m-t-05" alt="<?=$responseData->getLocalValue('globalLoading')?>">
      </div>
    </div>
  </div>
  <div>
    <label for="publishOn" class="label"><?=$responseData->getLocalValue('globalPublishOn')?>:</label>
    <div class="control article-publish-on">
      <label for="publishOnDate" class="label null">Date:</label>
      <input class="input flex-grow-4" id="publishOnDate" name="publishOnDate" type="date" placeholder="<?=$responseData->getLocalValue('globalDateFormat')?>" value="<?=$publishOnDate?>" required>
      <label for="publishOnTime" class="label null">Time:</label>
      <input class="input publish-on-time" id="publishOnTime" name="publishOnTime" type="time" placeholder="<?=$responseData->getLocalValue('globalDateFormat')?>" value="<?=$publishOnTime?>" required>
    </div>
    <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
  </div>
  <div>
    <button class="article-save-button button is-success" value="<?=$responseData->getLocalValue('globalSave')?>"><?=$responseData->getLocalValue('globalSave')?></button>
    <div class="m-t-05"><?=$article ? $createdAtContent : ''?></div>
  </div>
</div>
