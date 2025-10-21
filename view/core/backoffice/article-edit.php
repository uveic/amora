<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */
$article = $responseData->article;

$articleType = ArticleHtmlGenerator::getArticleType($responseData);

$this->layout('base', ['responseData' => $responseData]);
$this->insert('partials/shared/modal-select-image', ['responseData' => $responseData]);
?>
<main>
  <div id="feedback" class="feedback null"></div>
  <div class="page-header">
    <span class="back-js cursor-pointer"><?=CoreIcons::CARET_LEFT?></span>
    <span class="icon-one-line width-10-grow"><?=CoreIcons::ARTICLE?><span class="ellipsis"><?=$responseData->getLocalValue('globalEdit') . ': ' . $responseData->article->title?></span></span>
    <div class="links">
      <a href="<?=UrlBuilderUtil::buildBackofficeArticleListUrl(language: $responseData->siteLanguage)?>"><?=CoreIcons::LIST_BULLETS?></a>
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
