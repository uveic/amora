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
      <h1><?=$this->e($article ? 'Edit' : 'New')?> Article</h1>
      <div class="links">
        <a href="/backoffice/articles" style="font-size: 1.5rem;margin-right: 1rem;">&#10005;</a>
      </div>
    </div>
<?=$this->insert('partials/article-control-bar', ['responseData' => $responseData])?>
    <div class="content-medium-width">
      <input name="articleId" type="hidden" value="<?=$article ? $this->e($article->getId()) : ''?>">
      <div id="article-title" class="article-title input-div<?=$article && $article->getTitle() ? ' input-div-clean' : ''?>" contenteditable="true"><?=$this->e($article ? $article->getTitle() : ''); ?></div>
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
                'class="article-section-image-caption"',
                'class="article-section-image-caption" contenteditable="true"',
                $contentHtml
            );

            $contentHtml .= '<a href="#" class="article-section-image-delete" data-image-id="' . $articleSection->getImageId() . '">Remove from article</a>';
        }

        if ($articleSection->getArticleSectionTypeId() === ArticleSectionType::TEXT) {
            $editorIds[] = $editorId;
            $placeholder =' data-placeholder="Type something..."';
            $class .= ' placeholder null';
?>
        <div id="<?=$editorId?>">
          <div class="<?=$editorId?> pell-content" contenteditable="true"><?=$articleSection->getContentHtml()?></div>
        </div>
<?php } ?>
        <section id="<?=$editorId?>-html" class="<?=$class?>" data-section-id="<?=$articleSection->getId()?>"<?=$placeholder?>>
          <?=$contentHtml . PHP_EOL?>
        </section>
<?php } ?>
      </article>
    </div>
    <div class="article-add-sections">
      <input class="null" type="file" id="article-add-image-input" name="article-add-image-input" multiple="" accept="image/*">
      <label class="article-add-section-image article-add-section" for="article-add-image-input">
        <img class="img-svg" src="/img/assets/image.svg" alt="Add image">Add image(s)
      </label>
      <button class="article-add-section article-add-section-text"><img class="img-svg" src="/img/assets/article.svg" alt="Add text">Add text</button>
      <button class="article-add-section article-add-section-video"><img class="img-svg" src="/img/assets/youtube-logo.svg" alt="Add video">Add video</button>
    </div>
<?=$this->insert('partials/article-control-bar', ['responseData' => $responseData])?>
  </form>
</section>
<script src="/js/editor.js"></script>
<script>
<?php
    foreach ($editorIds as $editorId) {
        echo "loadEditor('" . $editorId . "');" . PHP_EOL;
    }
?>
</script>
