<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;

/** @var HtmlResponseDataAdmin $responseData */
$article = $responseData->article;

$articleType = ArticleHtmlGenerator::getArticleType($responseData);

$this->layout('base', ['responseData' => $responseData]);

?>
<?=$this->insert('partials/articles-edit/settings', ['responseData' => $responseData])?>
<section>
  <div id="feedback" class="feedback null"></div>
<?=$this->insert('partials/articles-edit/control-bar', ['responseData' => $responseData])?>
  <div class="article-wrapper">
    <input name="articleId" type="hidden" value="<?=$article ? $article->id : ''?>">
    <input name="articleTypeId" type="hidden" value="<?=$articleType->value?>">
    <article class="article-container">
      <label for="articleTitle" class="label null"><?=$responseData->getLocalValue('globalTitle')?>:</label>
      <input id="articleTitle" name="articleTitle" type="text" value="<?=$article ? $article->title: ''?>" placeholder="<?=$responseData->getLocalValue('editorTitlePlaceholder')?>" class="content-title">
      <div class="editor-content medium-editor-content m-t-1" contenteditable="true">
        <?=$article?->contentHtml . PHP_EOL?>
      </div>
    </article>
    </div>
</section>
