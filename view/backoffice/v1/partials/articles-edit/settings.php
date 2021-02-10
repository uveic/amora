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
        ? DateUtil::getElapsedTimeString($article->getCreatedAt(), $responseData->getSiteLanguage(), false, true) .
        ': ' . DateUtil::formatUtcDate($article->getCreatedAt(), $responseData->getSiteLanguage(), true, true, $responseData->getTimezone())
        : '');

?>
<div id="side-options" class="side-options null">
  <div class="side-options-header">
    <h2><?=$responseData->getLocalValue('navAdminArticleOptions')?></h2>
    <a href="#" class="close-button"><img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="Close"></a>
  </div>
  <div class="field">
    <label for="tags" class="label"><?=$responseData->getLocalValue('globalTags')?>:</label>
    <div id="tags-selected"></div>
    <div class="control">
      <input class="input" id="tags" name="tags" type="text" placeholder="<?=$responseData->getLocalValue('globalTags')?>" value="">
      <div class="search-wrapper">
        <div id="search-results-tags" class="search-results null">
          <a href="#" class="search-results-close"></a>
          <a href="#" class="search-results-close"><img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="Close"></a>
        </div>
      </div>
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
            <option <?php echo $selected ? 'selected' : ''; ?> value="<?=$this->e($type['id'])?>"><?=$responseData->getLocalValue('articleType' . $type['name'])?></option>
          <?php } ?>
      </select>
    </div>
  </div>
  <div class="control">
    <button class="button is-success m-b-1" value="<?=$responseData->getLocalValue('globalSave')?>"><?=$responseData->getLocalValue('globalSave')?></button>
  </div>
  <p><?=$article ? $createdAtContent : ''?></p>
</div>
