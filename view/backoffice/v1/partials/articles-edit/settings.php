<?php
use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Model\Tag;
use Amora\Core\Util\DateUtil;

/** @var HtmlResponseDataAuthorised $responseData */
$article = $responseData->getFirstArticle();

$articleTags = $article ? $article->getTags() : [];
$d = new DateTime();
$minPublishAt = $d->format('Y-m-d');

$publishOn = $article && $article->getPublishOn()
    ? DateUtil::transformFromUtcTo($article->getPublishOn(), $responseData->getSession()->getTimezone(), 'Y-m-d')
    : '';

$createdAtContent = '';
if ($article) {
    $createdAtContent = $responseData->getLocalValue('globalCreated') . ' ' .
        DateUtil::getElapsedTimeString(
            datetime: $article->getCreatedAt(),
            language: $responseData->getSiteLanguage(),
            includePrefixAndOrSuffix: true
        ) . ' ('
        . DateUtil::formatUtcDate(
            stringDate: $article->getCreatedAt(),
            lang: $responseData->getSiteLanguage(),
            includeWeekDay: true,
            includeTime: true,
            timezone: $responseData->getTimezone()
        ) . ')'
        . ' '
        . $responseData->getLocalValue('globalBy') . ' '
        . $article->getUser()->getName() . '.';
}

?>
<div id="side-options" class="side-options null">
  <div class="side-options-header">
    <h2><?=$responseData->getLocalValue('navAdminArticleOptions')?></h2>
    <a href="#" class="close-button"><img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="Close"></a>
  </div>
  <div class="field">
    <label for="tags" class="label"><?=$responseData->getLocalValue('globalTags')?>:</label>
    <div id="tags-selected" class="search-results-selected">
<?php
    /** @var Tag $tag */
    foreach ($articleTags as $tag) {
?>
      <span class="result-selected" data-tag-id="<?=$tag->getId()?>" data-tag-name="<?=$tag->getName()?>"><?=$tag->getName()?><img class="tag-result-selected-delete img-svg m-l-05" title="<?=$responseData->getLocalValue('globalRemove')?>" alt="<?=$responseData->getLocalValue('globalRemove')?>" src="/img/svg/x.svg"></span>
<?php } ?>
    </div>
    <div class="control">
      <input class="input search-input" id="tags" name="tags" type="text" placeholder="<?=$responseData->getLocalValue('globalTags')?>" value="">
      <div class="search-wrapper">
        <div id="search-results-tags" class="search-results null">
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
    <label for="publishOn" class="label"><?=$responseData->getLocalValue('globalPublishOn')?>:</label>
    <div class="control">
      <input class="input" id="publishOn" name="publishOn" type="date" placeholder="<?=$responseData->getLocalValue('globalDateFormat')?>" min="<?=$minPublishAt?>" value="<?=$publishOn?>" required>
    </div>
    <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span><?=$responseData->getLocalValue('globalFormat')?>: <i><?=$responseData->getLocalValue('globalDateFormat')?></i></p>
  </div>
  <div class="control">
    <button class="article-save-button button is-success m-b-1" value="<?=$responseData->getLocalValue('globalSave')?>"><?=$responseData->getLocalValue('globalSave')?></button>
  </div>
  <p><?=$article ? $createdAtContent : ''?></p>
</div>
