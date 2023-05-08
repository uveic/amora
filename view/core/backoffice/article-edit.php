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
      <h1 class="articleTitle content-title" contenteditable="true"><?=$article?->title?></h1>
      <div class="editor-content medium-editor-content m-t-1" contenteditable="true">
        <?=$article?->contentHtml . PHP_EOL?>
      </div>
    </article>
    </div>
</section>
