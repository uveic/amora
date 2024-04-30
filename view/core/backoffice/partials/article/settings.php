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

$createdAtContent = '';
if ($article) {
    $createdAtContent = $responseData->getLocalValue('globalCreated') . ' ' .
        DateUtil::getElapsedTimeString(
            from: $article->createdAt,
            includePrefixAndOrSuffix: true,
            language: $responseData->siteLanguage,
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

$articlePreviewUrl = $article ? UrlBuilderUtil::buildBackofficeArticlePreviewUrl(
    language: $responseData->siteLanguage,
    articleId: $article->id,
)
: '#';

$articlePublicUrl = $article ? UrlBuilderUtil::buildPublicArticlePath($article?->path, $article->language) : '';
$articlePublicUrlHtml = $articlePublicUrl && $article?->status->isPublic() ?
    '<a href="' . $articlePublicUrl . '">' . $articlePublicUrl . '</a>'
    : $articlePublicUrl;

?>
<div class="side-nav-wrapper">
  <div class="<?=$article ? '' : 'null'?>">
    <div class="label"><?=$responseData->getLocalValue('formArticlePath')?>:</div>
    <div class="editor-article-path"><?=$articlePublicUrlHtml?></div>
    <div class="editor-article-preview m-t-1"><a href="<?=$articlePreviewUrl?>"><?=$responseData->getLocalValue('globalPreview')?></a></div>
  </div>
  <a href="#" class="select-media-action" data-type-id="<?=MediaType::Image->value?>" data-event-listener-action="insertImageInArticle">
    <img class="img-svg img-svg-30" src="/img/svg/image.svg" alt="<?=$responseData->getLocalValue('globalAddImage')?>" title="<?=$responseData->getLocalValue('globalAddImage')?>">
  </a>
  <div>
<?=ArticleHtmlGenerator::generateArticleLanguageDropdownSelectHtml($responseData)?>
  </div>
  <div>
<?=ArticleHtmlGenerator::generateArticleStatusDropdownSelectHtml($responseData)?>
  </div>
  <div>
    <label class="label"><?=$responseData->getLocalValue('globalPublishOn')?>:</label>
    <div class="control two-columns m-t-025">
      <label for="publishOnDate" class="label null">Date:</label>
      <input class="input flex-grow-4" id="publishOnDate" name="publishOnDate" type="date" placeholder="<?=$responseData->getLocalValue('globalDateFormat')?>" value="<?=$publishOnDate?>" required>
      <label for="publishOnTime" class="label null">Time:</label>
      <input class="input publish-on-time" id="publishOnTime" name="publishOnTime" type="time" placeholder="<?=$responseData->getLocalValue('globalDateFormat')?>" value="<?=$publishOnTime?>" required>
    </div>
  </div>
  <div>
    <button class="article-save-js button is-success" value="<?=$responseData->getLocalValue('globalSave')?>"><?=$responseData->getLocalValue('globalSave')?></button>
    <div class="editor-article-info"><?=$createdAtContent?></div>
  </div>
</div>
