<?php
use uve\core\model\response\HtmlResponseDataAuthorised;
use uve\core\util\DateUtil;

/** @var HtmlResponseDataAuthorised $responseData */
$article = $responseData->getFirstArticle();

$d = new DateTime();
$minPublishAt = $d->format('Y-m-d');

$publishAt = $article && $article->getPublishedAt()
    ? DateUtil::transformFromUtcTo($article->getPublishedAt(), $responseData->getSession()->getTimezone(), 'Y-m-d')
    : '';

$createdAtContent = $responseData->getLocalValue('globalCreated') . ' ' . ($article
        ? '<span title="' .
        $this->e(DateUtil::formatUtcDate($article->getCreatedAt(), $responseData->getSiteLanguage(), true, true, $responseData->getTimezone())) .
        '">' . $this->e(DateUtil::getElapsedTimeString($article->getCreatedAt(), $responseData->getSiteLanguage(), false, true)) . '</span>.'
        : '<span class="article-created-at" title=""></span>');

?>
<div id="side-options" class="side-options null">
  <div class="side-options-header">
    <a href="#" class="close-button"><img src="/img/svg/x.svg" class="img-svg" style="max-width: 25px;" alt="Close"></a>
  </div>
  <h2><?=$responseData->getLocalValue('navAdminArticleOptions')?></h2>
  <div class="field">
    <label for="publishAt" class="label"><?=$responseData->getLocalValue('globalPublishOn')?>:</label>
    <div class="control">
      <input class="input" id="publishAt" name="publishAt" type="date" placeholder="<?=$responseData->getLocalValue('globalDateFormat')?>" min="<?=$minPublishAt?>" value="<?=$publishAt?>" required>
    </div>
    <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span><?=$responseData->getLocalValue('globalFormat')?>: <i><?=$responseData->getLocalValue('globalDateFormat')?></i></p>
  </div>
  <div class="field">
    <label for="typeId" class="label"><?=$responseData->getLocalValue('globalCategory')?>:</label>
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
  <div class="field">
    <label for="tags" class="label"><?=$responseData->getLocalValue('globalTags')?>:</label>
    <div class="control">
      <input class="input" id="tags" name="tags" type="text" placeholder="<?=$responseData->getLocalValue('globalTags')?>" value="">
    </div>
  </div>
  <div class="field">
    <label for="articleUri" class="label"><?=$responseData->getLocalValue('formArticleUri')?>:</label>
    <div class="control">
      <div class="article-edit-uri"><?=$this->e(trim($responseData->getBaseUrl(), ' /') . '/')?>
        <input id="articleUri" name="articleUri" class="is-light" type="text" placeholder="url" value="<?=$this->e($article ? $article->getUri() : ''); ?>">
      </div>
    </div>
  </div>
  <div class="control">
    <button class="button is-success m-b-1" value="<?=$responseData->getLocalValue('globalSave')?>"><?=$responseData->getLocalValue('globalSave')?></button>
  </div>
  <?=$article ? $createdAtContent : ''?>
</div>
