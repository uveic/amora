<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */

$articleType = ArticleHtmlGenerator::getArticleType($responseData);
$closeUrl = match($articleType) {
    ArticleType::Page => UrlBuilderUtil::buildBackofficeArticlesUrl($responseData->siteLanguage, ArticleType::Page),
    ArticleType::Blog => UrlBuilderUtil::buildBackofficeArticlesUrl($responseData->siteLanguage, ArticleType::Blog),
};

$this->insert('partials/articles-edit/modal-add-image', ['responseData' => $responseData]);
?>
  <div class="control-bar-wrapper">
    <div class="control-bar-left">
      <?=ArticleHtmlGenerator::generateSettingsButtonHtml($responseData);?>
      <a href="#" class="select-media-action" data-type-id="<?=MediaType::Image->value?>" data-event-listener-action="insertImageInArticle">
        <img class="img-svg img-svg-30" src="/img/svg/image.svg" alt="<?=$responseData->getLocalValue('globalAddImage')?>" title="<?=$responseData->getLocalValue('globalAddImage')?>">
      </a>
    </div>
    <div class="control-bar-right">
      <a href="<?=$closeUrl?>"><?=$responseData->getLocalValue('globalClose')?></a>
      <button class="article-save-button button"><?=$responseData->getLocalValue('globalSave')?></button>
    </div>
  </div>
