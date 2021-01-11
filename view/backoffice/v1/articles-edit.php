<?php

use uve\core\module\article\model\Image;
use uve\core\model\response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */
$article = $responseData->getFirstArticle();
$images = [];

$this->layout('base', ['responseData' => $responseData]);

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
<?php if (!empty($article)) { ?>
      <input name="articleId" type="hidden" value="<?=$this->e($article->getId()); ?>">
<?php } ?>
      <div id="article-title" class="article-title input-div<?=$article && $article->getTitle() ? ' input-div-clean' : ''?>" contenteditable="true"><?=$this->e($article ? $article->getTitle() : ''); ?></div>
      <div class="article-edit-uri"><?=$this->e(trim($responseData->getBaseUrl(), ' /') . '/')?>
        <input name="uri" class="is-light" type="text" placeholder="url" value="<?=$this->e($article ? $article->getUri() : ''); ?>">
      </div>
      <div id="pell" class="pell article-pell">
        <div class="pell-content" contenteditable="true"><?=$article ? $article->getContent() : ''?></div>
      </div>
      <article class="article-content">
        <section id="content-html" class="null article-section article-section-text">
            <?=$article ? $article->getContent() : ''?>
        </section>
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
    <div class="control m-t-6 m-b-6" style="text-align: center;">
<?php if (!empty($article)) { ?>
      <a href="#" class="is-danger">Delete</a>
<?php } ?>
    </div>
  </form>
</section>
<script src="/js/pell.js"></script>
