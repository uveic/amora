<?php

use uve\core\model\response\HtmlResponseDataAuthorised;
use uve\core\module\article\model\ArticleSection;
use uve\core\module\article\value\ArticleSectionType;

/** @var HtmlResponseDataAuthorised $responseData */
$article = $responseData->getFirstArticle();
$articleSections = $responseData->getArticleSections();
$images = [];
$editorIds = [];

$this->layout('base', ['responseData' => $responseData]);

function getClassName(int $sectionTypeId): string
{
  return match ($sectionTypeId) {
      ArticleSectionType::TEXT => 'article-section-text',
      ArticleSectionType::IMAGE => 'article-section-image',
      ArticleSectionType::YOUTUBE_VIDEO => 'article-section-video'
  };
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
        $editorId = getClassName($articleSection->getArticleSectionTypeId()) . '-' . $articleSection->getId();
        $class = 'article-section ' . getClassName($articleSection->getArticleSectionTypeId());
        $contentEditable = '';
        $placeholder = '';
        $contentHtml = $articleSection->getContentHtml();
        if ($articleSection->getArticleSectionTypeId() === ArticleSectionType::IMAGE) {
            $contentHtml = str_replace(
                'article-section-image-caption"',
                'article-section-image-caption" contenteditable="true"',
                $contentHtml
            );

            $contentHtml .= '<div class="article-section-image-control">';
            $contentHtml .= '<a href="#" class="article-section-control-button article-section-control-up" data-image-id="' . $articleSection->getImageId() . '"><img src="/img/assets/arrow-fat-up.svg" class="img-svg" alt="Move image up"></a>';
            $contentHtml .= '<a href="#" class="article-section-control-button article-section-control-down" data-image-id="' . $articleSection->getImageId() . '"><img src="/img/assets/arrow-fat-down.svg" class="img-svg" alt="Move image down"></a>';
            $contentHtml .= '<a href="#" class="article-section-control-button article-section-control-delete" data-image-id="' . $articleSection->getImageId() . '"><img src="/img/assets/trash.svg" class="img-svg" alt="Remove image from article"></a>';
            $contentHtml .= '</div>';
        }

        if ($articleSection->getArticleSectionTypeId() === ArticleSectionType::TEXT) {
            $editorIds[] = $editorId;
            $placeholder =' data-placeholder="Type something..."';
            $class .= ' placeholder null';
?>
        <section id="<?=$editorId?>" class="article-section article-content <?=getClassName($articleSection->getArticleSectionTypeId())?>" data-section-id="<?=$articleSection->getId()?>">
          <div class="<?=$editorId?> placeholder pell-content" contenteditable="true"><?=$articleSection->getContentHtml()?></div>
        </section>
<?php } else { ?>
        <section class="<?=$class?>" data-section-id="<?=$articleSection->getId()?>">
            <?=$contentHtml . PHP_EOL?>
        </section>
<?php } ?>
<?php } ?>
      </article>
      <div class="article-content-text null">
<?php
    foreach ($articleSections as $articleSection) {
        $editorId = getClassName($articleSection->getArticleSectionTypeId()) . '-' . $articleSection->getId();
        if ($articleSection->getArticleSectionTypeId() === ArticleSectionType::TEXT) {
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
