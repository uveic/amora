<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */

$article = $responseData->article;

$articleStatus = $article ? $article->status : ArticleStatus::Draft;
$articleStatusName = $responseData->getLocalValue('articleStatus' . $articleStatus->name);
$isPublished = $article && $articleStatus === ArticleStatus::Published;

$random = StringUtil::generateRandomString(5);

$articleUrl = $article
    ? UrlBuilderUtil::buildPublicArticlePath(
        path: $article->path,
        language: $responseData->siteLanguage,
    )
    : '#';

$articleType = ArticleHtmlGenerator::getArticleType($responseData);
$closeUrl = match($articleType) {
    ArticleType::Page => UrlBuilderUtil::buildBackofficeArticlesUrl($responseData->siteLanguage, ArticleType::Page),
    ArticleType::Blog => UrlBuilderUtil::buildBackofficeArticlesUrl($responseData->siteLanguage, ArticleType::Blog),
};

$this->insert('partials/articles-edit/modal-add-image', ['responseData' => $responseData]);
?>
  <div class="control-bar-wrapper">
    <div class="control-bar-left">
<?=ArticleHtmlGenerator::generateArticleLanguageDropdownSelectHtml($responseData)?>
<?=ArticleHtmlGenerator::generateArticleStatusDropdownSelectHtml($responseData)?>
      <a href="#" class="select-media-action" data-type-id="<?=MediaType::Image->value?>" data-event-listener-action="insertImageInArticle">
        <img class="img-svg img-svg-30" src="/img/svg/image.svg" alt="<?=$responseData->getLocalValue('globalAddImage')?>" title="<?=$responseData->getLocalValue('globalAddImage')?>">
      </a>
      <a href="<?=$articleUrl?>" class="article-preview<?=$article ? '' : ' null'?>" target="_blank">
        <img class="img-svg img-svg-30" src="/img/svg/arrow-square-out.svg" alt="<?=$responseData->getLocalValue('globalPreview')?>" title="<?=$responseData->getLocalValue('globalPreview')?>">
      </a>
    </div>
    <div class="control-bar-right">
<?=ArticleHtmlGenerator::generateSettingsButtonHtml($responseData);?>
      <a href="<?=$closeUrl?>"><img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalClose')?>"></a>
      <button class="article-save-button button"><?=$article ? $responseData->getLocalValue('globalUpdate') : $responseData->getLocalValue('globalSave')?></button>
    </div>
  </div>
