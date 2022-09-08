<?php

use Amora\Core\Entity\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$article = $responseData->article;

$articleStatus = $article ? $article->status : ArticleStatus::Draft;
$articleStatusName = $responseData->getLocalValue('articleStatus' . $articleStatus->name);
$isPublished = $article && $articleStatus === ArticleStatus::Published;

$random = StringUtil::getRandomString(5);

$articleUrl = $article
    ? UrlBuilderUtil::buildPublicArticleUrl(
        uri: $article->uri,
        language: $responseData->siteLanguage,
    )
    : '#';

$this->insert('partials/articles-edit/modal-add-image', ['responseData' => $responseData]);
?>
  <div class="control-bar-wrapper">
    <div class="pexego-tools-amora">
      <div class="pexego-actions-amora-wrapper">
        <button class="article-save-button button"><?=$article ? $responseData->getLocalValue('globalUpdate') : $responseData->getLocalValue('globalSave')?></button>
        <div class="pexego-actions-amora">
          <a href="#" class="pexego-add-section pexego-add-section-paragraph">
            <img class="img-svg img-svg-30" src="/img/svg/article.svg" alt="<?=$responseData->getLocalValue('globalAddParagraph')?>">
          </a>
          <a href="#" class="pexego-add-section article-add-media" data-type-id="<?=MediaType::Image->value?>">
            <img class="img-svg img-svg-30" src="/img/svg/image-black.svg" alt="<?=$responseData->getLocalValue('globalAddImage')?>" title="<?=$responseData->getLocalValue('globalAddImage')?>">
          </a>
          <a class="pexego-add-section pexego-add-section-video">
            <img class="img-svg img-svg-30" src="/img/svg/youtube-logo.svg" alt="<?=$responseData->getLocalValue('globalAddVideo')?>" title="<?=$responseData->getLocalValue('globalAddVideo')?>">
          </a>
          <a href="#" class="pexego-rearrange-sections-button">
            <img class="img-svg img-svg-30" src="/img/svg/arrows-down-up.svg" title="<?=$responseData->getLocalValue('editorEnableControls')?>" alt="<?=$responseData->getLocalValue('editorEnableControls')?>">
          </a>
          <a href="<?=$articleUrl?>" class="pexego-preview<?=$article ? '' : ' null'?>" target="_blank">
            <img class="img-svg img-svg-30" src="/img/svg/arrow-square-out.svg" alt="<?=$responseData->getLocalValue('globalPreview')?>" title="<?=$responseData->getLocalValue('globalPreview')?>">
          </a>
        </div>
        <a href="#" class="pexego-rearrange-sections-close null">
          <img class="img-svg img-svg-30" src="/img/svg/x.svg" title="<?=$responseData->getLocalValue('globalClose')?>" alt="<?=$responseData->getLocalValue('globalClose')?>">
        </a>
      </div>
    </div>
    <div class="article-tools">
<?=ArticleEditHtmlGenerator::generateArticleLanguageDropdownSelectHtml($responseData)?>
<?=ArticleEditHtmlGenerator::generateArticleStatusDropdownSelectHtml($responseData)?>
    </div>
  </div>
