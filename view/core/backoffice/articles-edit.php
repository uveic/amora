<?php

use Amora\Core\Entity\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Model\ArticleSection;
use Amora\Core\Module\Article\Value\ArticleSectionType;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;

/** @var HtmlResponseDataAuthorised $responseData */
$article = $responseData->article;
$articleSections = $responseData->articleSections;

$articleType = ArticleEditHtmlGenerator::getArticleType($responseData);

if (!$articleSections) {
    $now = new DateTimeImmutable();
    $articleSections[] = new ArticleSection(
        id: 0,
        articleId: 0,
        articleSectionType: ArticleSectionType::TextParagraph,
        contentHtml: '',
        order: null,
        mediaId: null,
        mediaCaption: null,
        createdAt: $now,
        updatedAt: $now,
    );
}

$this->layout('base', ['responseData' => $responseData]);

?>
<?=$this->insert('partials/articles-edit/settings', ['responseData' => $responseData])?>
<section>
  <div id="feedback" class="feedback null"></div>
  <form action="#">
<?=$this->insert('partials/articles-edit/header', ['responseData' => $responseData])?>
    <div>
      <input name="articleId" type="hidden" value="<?=$article ? $article->id : ''?>">
      <input name="articleTypeId" type="hidden" value="<?=$articleType->value?>">
      <article class="pexego-container">
<?php if (!ArticleType::isPartialContent($articleType)) { ?>
        <input name="articleTitle" type="text" value="<?=$article ? $article->title: ''?>" placeholder="<?=$responseData->getLocalValue('editorTitlePlaceholder')?>" class="pexego-content-title placeholder">
<?php
}
    $count = 0;
    $total = count($articleSections);
    /** @var ArticleSection $articleSection */
    foreach ($articleSections as $articleSection) {
?>
        <div id="pexego-section-wrapper-<?=$articleSection->id?>" class="pexego-section-wrapper" data-section-id="<?=$articleSection->id?>">
<?=ArticleEditHtmlGenerator::generateSection($responseData, $articleSection)?>
<?=ArticleEditHtmlGenerator::getControlButtonsHtml(
    responseData: $responseData,
    sectionId: $articleSection->id,
    isFirst: $count === 0,
    isLast: $count === $total - 1,
)?>
        </div>
<?php
        $count++;
    }
?>
      </article>
      <div class="pexego-container-output null">
<?php
    foreach ($articleSections as $articleSection) {
        if ($articleSection->articleSectionType === ArticleSectionType::TextParagraph) {
            $editorId = ArticleEditHtmlGenerator::getClassName($articleSection->articleSectionType) . '-' . $articleSection->id;
?>
        <div id="<?=$editorId?>-html">
          <?=$articleSection->contentHtml . PHP_EOL?>
        </div>
<?php } } ?>
      </div>
    </div>
<?=$this->insert('partials/articles-edit/control-bar', ['responseData' => $responseData])?>
  </form>
</section>
