<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */
$article = $responseData->article;

$articleType = ArticleHtmlGenerator::getArticleType($responseData);

$closeLink = match($articleType) {
    ArticleType::Page => UrlBuilderUtil::buildBackofficeArticleListUrl($responseData->siteLanguage, ArticleType::Page),
    ArticleType::Blog => UrlBuilderUtil::buildBackofficeArticleListUrl($responseData->siteLanguage, ArticleType::Blog),
};

$this->layout('base', ['responseData' => $responseData]);
$this->insert('partials/shared/modal-select-image', ['responseData' => $responseData]);
?>
<main>
  <div id="feedback" class="feedback null"></div>
  <div class="page-header">
    <div></div>
    <div class="links">
      <a href="<?=$closeLink?>"><img src="/img/svg/x.svg" class="img-svg img-svg-30" width="20" height="20" alt="<?=$responseData->getLocalValue('globalClose')?>"></a>
    </div>
  </div>

  <article class="main-wrapper">
    <div class="main-inner">
      <input name="articleId" type="hidden" value="<?=$article ? $article->id : ''?>">
      <input name="articleTypeId" type="hidden" value="<?=$articleType->value?>">
      <div class="page-content-before"><?=$responseData->getLocalValue('globalTitle')?></div>
      <h1 class="editor-title page-content-title<?=$article?->title ? '' : ' editor-placeholder'?>" contenteditable="true"><?=$article?->title ?: $responseData->getLocalValue('editorTitlePlaceholder')?></h1>
      <div class="page-content-before"><?=$responseData->getLocalValue('navAdminContent')?></div>
      <div class="editor-content medium-editor-content" contenteditable="true">
        <?=$article?->contentHtml . PHP_EOL?>
      </div>
    </div>
<?=$this->insert('partials/article/settings', ['responseData' => $responseData]);?>
  </article>
</main>
