<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

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
      <a href="<?=$closeLink?>"><?=CoreIcons::CLOSE?></a>
    </div>
  </div>

  <article class="main-wrapper">
    <div class="main-inner">
      <input name="articleId" type="hidden" value="<?=$article ? $article->id : ''?>">
      <input name="articleTypeId" type="hidden" value="<?=$articleType->value?>">
      <input id="trixEditorContentHtml" name="articleContentHtml" type="hidden" value="<?=htmlspecialchars($article?->contentHtml ?? '')?>">
      <label for="articleTitle" class="page-content-before"><?=$responseData->getLocalValue('globalTitle')?></label>
      <input id="articleTitle" name="articleTitle" maxlength="255" class="editor-title" placeholder="<?=$responseData->getLocalValue('editorTitlePlaceholder')?>" value="<?=$article?->title?>">
      <div class="page-content-before"><?=$responseData->getLocalValue('navAdminContent')?></div>
<?php $this->insert('../shared/trix-editor', ['responseData' => $responseData]); ?>
    </div>
<?=$this->insert('partials/article/settings', ['responseData' => $responseData]);?>
  </article>
</main>
