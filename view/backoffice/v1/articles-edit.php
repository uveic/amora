<?php

use uve\core\module\article\model\Image;
use uve\core\model\response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */
$article = $responseData->getFirstArticle();
$images = $article ? $article->getImages() : [];

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
      <form id="article" action="#">
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
          <section id="content-html" class="null article-section-text">
              <?=$article ? $article->getContent() : ''?>
          </section>
        </article>
    </div>
    <div class="article-add-sections">
      <input class="null" type="file" id="article-add-image-input" name="article-add-image-input" multiple="" accept="image/*">
      <label class="article-add-item-image" for="article-add-image-input">
        <span class="m-r-05"><svg xmlns="http://www.w3.org/2000/svg" width="192" height="192" fill="#fcfcfc" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><rect x="32" y="48" width="192" height="160" rx="8" stroke-width="16" stroke="#fcfcfc" stroke-linecap="round" stroke-linejoin="round" fill="none"></rect><path d="M32,167.99982l50.343-50.343a8,8,0,0,1,11.31371,0l44.68629,44.6863a8,8,0,0,0,11.31371,0l20.68629-20.6863a8,8,0,0,1,11.31371,0L223.99982,184" fill="none" stroke="#fcfcfc" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path><circle cx="156" cy="100" r="12"></circle></svg></span> Add image(s)
      </label>
    </div>
    <div class="content-images">
      <div class="field m-t-0 m-b-0">
        <div id="upload-images">
          <div id="upload-images-info">
            <h1>Images</h1>
          </div>
          <div id="upload-images-control">
            <input class="null" type="file" id="images" name="images" multiple="" accept="image/*">
            <label for="images" class="input-file-label"> ⇪ Upload image(s)</label>
          </div>
        </div>
      </div>
      <div id="images-list">
<?php
    /** @var Image $image */
    foreach ($images as $image) {
?>
        <div class="image-item" data-image-id="<?=$this->e($image->getId())?>">
          <img src="<?=$image->getFullUrlBig()?>" title="<?=$this->e($image->getCaption())?>" alt="<?=$this->e($image->getCaption())?>" data-image-id="<?=$image->getId()?>">
          <div id="image-options-<?=$this->e($image->getId())?>" class="options null">
            <a class="image-delete" href="#">&#10006;</a>
          </div>
        </div>
<?php } ?>
      </div>

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
