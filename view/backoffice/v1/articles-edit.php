<?php

use uve\core\module\article\model\Image;
use uve\core\model\response\HtmlResponseDataAuthorised;
use uve\core\module\article\value\ArticleStatus;
use uve\core\util\DateUtil;

/** @var HtmlResponseDataAuthorised $responseData */
$article = $responseData->getFirstArticle();
$images = $article ? $article->getImages() : [];

$this->layout('base', ['responseData' => $responseData]);

$updatedAtContent = $article
    ? 'Updated <span class="articleUpdatedAt" title="' .
    $this->e(DateUtil::formatUtcDate($article->getUpdatedAt(), $responseData->getSiteLanguage(), true, true, $responseData->getTimezone())) .
    '">' . $this->e(DateUtil::getElapsedTimeString($article->getUpdatedAt(), $responseData->getSiteLanguage(), false, true)) . '</span>.'
    : '';

$createdAtContent = $article
    ? 'Created <span title="' .
    $this->e(DateUtil::formatUtcDate($article->getCreatedAt(), $responseData->getSiteLanguage(), true, true, $responseData->getTimezone())) .
    '">' . $this->e(DateUtil::getElapsedTimeString($article->getCreatedAt(), $responseData->getSiteLanguage(), false, true)) . '</span>.'
    : '';

$isPublished = $article
    ? $article->getStatusId() === ArticleStatus::PUBLISHED
    : false;

$statusName = $article
    ? ArticleStatus::getNameForId($article->getStatusId())
    : ArticleStatus::getNameForId(ArticleStatus::DRAFT);

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
    <div class="form-control-bar-header m-b-3">
      <input style="width: revert;" type="submit" class="button m-r-1" value="<?=$article ? 'Update' : 'Save'?>">
      <input style="width: revert;" type="submit" class="button" data-close="1" value="<?=$article ? 'Update & Close' : 'Save & Close'?>">
      <div style="text-align: right"><?=$updatedAtContent?><br><?=$createdAtContent?></div>
      <div data-enabled="<?=$isPublished ? '1' : ''?>" class="article-status enabled-icon-big <?=$isPublished ? 'enabled-icon-yes' : 'enabled-icon-no-light' ?>"> <?=$statusName?></div>
    </div>
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
        <div class="null" id="content-html"><?=$article ? $article->getContent() : ''?></div>
        <div>
          <div class="field">
            <label for="statusId" class="label">Status</label>
            <div class="control">
              <select id="statusId" name="statusId">
<?php
    $getFromArticle = $article ? true : false;
    foreach ($responseData->getArticleStatuses() as $status) {
        $selected = $getFromArticle
            ? $article && $status['id'] == $article->getStatusId()
            : $status['id'] == ArticleStatus::DRAFT;
?>
                <option <?php echo $selected ? 'selected' : ''; ?> value="<?=$this->e($status['id'])?>"><?=$this->e($status['name'])?></option>
<?php } ?>
              </select>
            </div>
          </div>
          <div class="field">
            <label for="typeId" class="label">Type</label>
            <div class="control">
              <select id="typeId" name="typeId">
<?php
    foreach ($responseData->getArticleTypes() as $type) {
        $selected = $article && $type['id'] == $article->getTypeId();
?>
                <option <?php echo $selected ? 'selected' : ''; ?> value="<?=$this->e($type['id'])?>"><?=$this->e($type['name'])?></option>
<?php } ?>
              </select>
            </div>
          </div>
        </div>
    </div>
    <div class="content-images">
      <div class="field m-t-0 m-b-0">
        <div id="upload-images">
          <div id="upload-images-info">
            <h1>Images</h1>
          </div>
          <div id="upload-images-control">
            <input class="null" type="file" id="images" name="images" multiple="" accept="image/*">
            <label for="images"> ⇪ Upload image(s)</label>
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
    <div class="form-control-bar-header m-b-3">
      <input style="width: revert;" type="submit" class="button m-r-1" value="<?=$article ? 'Update' : 'Save'?>">
      <input style="width: revert;" type="submit" class="button" data-close="1" value="<?=$article ? 'Update & Close' : 'Save & Close'?>">
      <div style="text-align: right"><?=$updatedAtContent?><br><?=$createdAtContent?></div>
      <div data-enabled="<?=$isPublished ? '1' : ''?>" class="article-status enabled-icon-big <?=$isPublished ? 'enabled-icon-yes' : 'enabled-icon-no-light' ?>"> <?=$statusName?></div>
    </div>
    <div class="control m-t-6 m-b-6" style="text-align: center;">
<?php if (!empty($article)) { ?>
      <a href="#" class="is-danger">Delete</a>
<?php } ?>
    </div>
  </form>
</section>
<script src="/js/pell.js"></script>
