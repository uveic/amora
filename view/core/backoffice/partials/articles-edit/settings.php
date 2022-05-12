<?php
use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Model\Tag;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAuthorised $responseData */
$article = $responseData->getFirstArticle();

$publishOnDate = '';
$publishOnTime = '';
if ($article && $article->publishOn) {
    $publishOnDate = $article->publishOn->format('Y-m-d');
    $publishOnTime = $article->publishOn->format('H:i');
}

$tags = [];
$createdAtContent = '';
if ($article) {
    $tags = $article->tags;
    $createdAtContent = $responseData->getLocalValue('globalCreated') . ' ' .
        DateUtil::getElapsedTimeString(
            language: $responseData->siteLanguage,
            from: $article->createdAt,
            includePrefixAndOrSuffix: true,
        ) . ' ('
        . DateUtil::formatDate(
            date: $article->createdAt,
            lang: $responseData->siteLanguage,
            includeWeekDay: true,
            includeTime: true,
        ) . ')'
        . ' '
        . $responseData->getLocalValue('globalBy') . ' '
        . $article->user->name . '.';
}

?>
<div id="side-options" class="side-options null">
  <div class="side-options-header">
    <h2><?=$responseData->getLocalValue('navAdminArticleOptions')?></h2>
    <a href="#" class="close-button"><img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalClose')?>"></a>
  </div>
  <div class="m-t-1">
    <label for="tags" class="label"><?=$responseData->getLocalValue('globalTags')?>:</label>
    <div id="tags-selected" class="search-results-selected<?=$tags ? '' : ' null'?>">
<?php
    /** @var Tag $tag */
    foreach ($tags as $tag) {
?>
      <span class="result-selected" data-tag-id="<?=$tag->id?>" data-tag-name="<?=$tag->name?>"><?=$tag->name?><img class="tag-result-selected-delete img-svg m-l-05" title="<?=$responseData->getLocalValue('globalRemove')?>" alt="<?=$responseData->getLocalValue('globalRemove')?>" src="/img/svg/x.svg"></span>
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
  <div class="m-t-2">
    <label for="articleUri" class="label"><?=$responseData->getLocalValue('formArticleUri')?>:</label>
    <div class="control">
      <div class="article-edit-uri"><?=UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage) . '/'?>
        <input id="articleUri" name="articleUri" class="is-light" type="text" placeholder="url" value="<?=$this->e($article ? $article->uri : ''); ?>">
      </div>
    </div>
  </div>
  <div class="m-t-2">
    <label for="publishOn" class="label"><?=$responseData->getLocalValue('globalPublishOn')?>:</label>
    <div class="control" style="display: flex;align-content: space-between;">
      <input style="flex-grow:4;" class="input" id="publishOnDate" name="publishOnDate" type="date" placeholder="<?=$responseData->getLocalValue('globalDateFormat')?>" value="<?=$publishOnDate?>" required>
      <div style="padding: 0 0.5rem;"></div>
      <input style="flex-grow:1;width: 130px;" class="input" id="publishOnTime" name="publishOnTime" type="time" placeholder="<?=$responseData->getLocalValue('globalDateFormat')?>" value="<?=$publishOnTime?>" required>
    </div>
    <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
  </div>
  <div class="m-t-1">
    <button class="article-save-button button is-success m-b-1" value="<?=$responseData->getLocalValue('globalSave')?>"><?=$responseData->getLocalValue('globalSave')?></button>
  </div>
  <div><?=$article ? $createdAtContent : ''?></div>
</div>
