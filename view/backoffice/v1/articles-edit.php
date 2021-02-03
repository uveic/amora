<?php

use uve\core\model\response\HtmlResponseDataAuthorised;
use uve\core\module\article\model\ArticleSection;
use uve\core\module\article\value\ArticleSectionType;

/** @var HtmlResponseDataAuthorised $responseData */
$article = $responseData->getFirstArticle();
$articleSections = $responseData->getArticleSections();
$images = [];

$this->layout('base', ['responseData' => $responseData]);

function getClassName(int $sectionTypeId): string
{
  return match ($sectionTypeId) {
      ArticleSectionType::TEXT_PARAGRAPH => 'article-section-paragraph',
      ArticleSectionType::TEXT_TITLE => 'article-section-title article-title placeholder',
      ArticleSectionType::TEXT_SUBTITLE => 'article-section-subtitle article-subtitle placeholder',
      ArticleSectionType::IMAGE => 'article-section-image',
      ArticleSectionType::YOUTUBE_VIDEO => 'article-section-video'
  };
}

function getControlButtonsHtml(int $sectionId): string
{
    return PHP_EOL . '<div class="article-section-controls">' . PHP_EOL
        . '<a href="#" id="article-section-button-up-' . $sectionId . '" class="article-section-button article-section-button-up"><img class="img-svg" title="Move Up" alt="Move Up" src="/img/svg/arrow-fat-up.svg"></a>' . PHP_EOL
        . '<a href="#" id="article-section-button-down-' . $sectionId . '" class="article-section-button article-section-button-down"><img class="img-svg" title="Move Down" alt="Move Down" src="/img/svg/arrow-fat-down.svg"></a>' . PHP_EOL
        . '<a href="#" id="article-section-button-delete-' . $sectionId . '" class="article-section-button article-section-button-delete"><img class="img-svg" title="Remove from article" alt="Remove from article" src="/img/svg/trash.svg"></a>' . PHP_EOL
        . '</div>' . PHP_EOL;
}

function generateSection(ArticleSection $articleSection): string
{
    if ($articleSection->getArticleSectionTypeId() === ArticleSectionType::TEXT_PARAGRAPH) {
        $class = getClassName($articleSection->getArticleSectionTypeId());
        $id = $class . '-' . $articleSection->getId();
        return '<section id="' . $id . '" data-editor-id="' . $articleSection->getId() . '" class="article-section article-content article-section-paragraph placeholder" data-placeholder="Type something...">' . PHP_EOL
            . '<div class="pell-content ' . $id . '" contenteditable="true">' . $articleSection->getContentHtml() . '</div>' . PHP_EOL
            . '</section>';
    }

    $class = 'article-section ' . getClassName($articleSection->getArticleSectionTypeId());
    $contentEditable = '';
    $placeholder = '';
    $contentHtml = $articleSection->getContentHtml();
    if ($articleSection->getArticleSectionTypeId() === ArticleSectionType::TEXT_TITLE ||
        $articleSection->getArticleSectionTypeId() === ArticleSectionType::TEXT_SUBTITLE
    ) {
        $contentEditable = ' contenteditable="true"';
        $placeholder =' data-placeholder="Type something..."';
    }

    if ($articleSection->getArticleSectionTypeId() === ArticleSectionType::TEXT_PARAGRAPH) {
        $class .= ' placeholder null';
    }

    if ($articleSection->getArticleSectionTypeId() === ArticleSectionType::IMAGE) {
        $contentHtml = str_replace(
            'article-section-image-caption"',
            'article-section-image-caption" contenteditable="true"',
            $contentHtml
        );
    }

  return '<section class="' . $class . '" data-section-id="' . $articleSection->getId() . '"' . $contentEditable . $placeholder . '>'  . PHP_EOL
      . $contentHtml . PHP_EOL
      . '</section>';
}

?>
<section>
  <div id="feedback" class="feedback null"></div>
  <form id="form-article" action="#">
    <div class="form-header m-t-1 m-l-1 m-r-1">
      <h1><?=($article ? $responseData->getLocalValue('globalEdit') : $responseData->getLocalValue('globalNew')) . ' ' . $responseData->getLocalValue('globalArticle')?></h1>
      <div class="links">
        <a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/articles" style="font-size: 1.5rem;margin-right: 1rem;">&#10005;</a>
      </div>
    </div>
<?=$this->insert('partials/articles-edit/control-bar', ['responseData' => $responseData])?>
    <div class="content-medium-width">
      <input name="articleId" type="hidden" value="<?=$article ? $this->e($article->getId()) : ''?>">
      <div id="article-title" class="article-title placeholder" contenteditable="true" data-placeholder="<?=$responseData->getLocalValue('globalTitle')?>"><?=$this->e($article ? $article->getTitle() : ''); ?></div>
      <div class="article-edit-uri"><?=$this->e(trim($responseData->getBaseUrl(), ' /') . '/')?>
        <input name="uri" class="is-light" type="text" placeholder="url" value="<?=$this->e($article ? $article->getUri() : ''); ?>">
      </div>
      <article class="article-content">
<?php
    /** @var ArticleSection $articleSection */
    foreach ($articleSections as $articleSection) {
?>
        <div id="article-section-wrapper-<?=$articleSection->getId()?>" class="article-section-wrapper" data-section-id="<?=$articleSection->getId()?>">
          <?=generateSection($articleSection)?>
          <?=getControlButtonsHtml($articleSection->getId())?>
        </div>
<?php } ?>
      </article>
      <div class="article-content-text null">
<?php
    foreach ($articleSections as $articleSection) {
        if ($articleSection->getArticleSectionTypeId() === ArticleSectionType::TEXT_PARAGRAPH) {
            $editorId = getClassName($articleSection->getArticleSectionTypeId()) . '-' . $articleSection->getId();
?>
        <div id="<?=$editorId?>-html">
            <?=$articleSection->getContentHtml() . PHP_EOL?>
        </div>
<?php } } ?>
      </div>
    </div>
<?=$this->insert('partials/articles-edit/add-sections', ['responseData' => $responseData])?>
<?=$this->insert('partials/articles-edit/control-bar', ['responseData' => $responseData])?>
  </form>
</section>
