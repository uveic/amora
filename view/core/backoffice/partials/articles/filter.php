<?php

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;

/** @var HtmlResponseDataAuthorised $responseData */

$articleTypeIdGetParam = $responseData->request->getGetParam('atId');
$articleType = !empty($articleTypeIdGetParam) && ArticleType::tryFrom($articleTypeIdGetParam)
    ? ArticleType::from($articleTypeIdGetParam)
    : null;

$articleLanguageIsoCodeGetParam = $responseData->request->getGetParam('lang');
$articleLanguage = $articleLanguageIsoCodeGetParam && Language::tryFrom($articleLanguageIsoCodeGetParam)
    ? Language::from($articleLanguageIsoCodeGetParam)
    : null;

$articleStatusGetParam = $responseData->request->getGetParam('status');
$articleStatus = !empty($articleStatusGetParam) && ArticleStatus::tryFrom($articleStatusGetParam)
    ? ArticleStatus::from($articleStatusGetParam)
    : null;

$filterClass = $articleStatus || $articleLanguage ? '' : 'null';
?>
    <section id="filter-container" class="<?=$filterClass?>">
      <div id="filter-header">
        <h3><?=$responseData->getLocalValue('formFilterTitle')?></h3>
        <div id="filter-links">
          <a href="#" id="filter-article-refresh"><img src="/img/svg/arrow-counter-clockwise.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('formFilterClean')?>"></a>
          <a href="#" id="filter-close"><img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalClose')?>"></a>
        </div>
      </div>
      <div id="filter-form-wrapper">
        <div class="field">
          <label for="articleType" class="label"><?=$responseData->getLocalValue('formFilterArticleTypeTitle')?></label>
          <div class="control">
            <select id="articleType" name="articleType">
<?php
    /** @var \BackedEnum $type */
    foreach (ArticleType::getAll() as $type) {
?>
              <option<?php echo $type === $articleType ? ' selected="selected"' : '';?> value="<?=$type->value?>"><?=$responseData->getLocalValue('articleType' . $type->name)?></option>
<?php } ?>
            </select>
          </div>
        </div>
        <div class="field">
          <label for="articleLanguageIsoCode" class="label"><?=$responseData->getLocalValue('globalLanguage')?></label>
          <div class="control">
            <select id="articleLanguageIsoCode" name="articleLanguageIsoCode">
              <option<?php echo $articleLanguage ? '' : ' selected="selected"'; ?> value=""></option>
<?php
    /** @var \BackedEnum $language */
    foreach (Core::getAllLanguages() as $language) {
        $selected = $language === $articleLanguage;
?>
              <option<?php echo $selected ? ' selected="selected"' : ''; ?> value="<?=$language->value?>"><?=$language->name?></option>
<?php } ?>
            </select>
          </div>
        </div>
        <div class="field">
          <label for="articleStatus" class="label"><?=$responseData->getLocalValue('globalStatus')?></label>
          <div class="control">
            <select id="articleStatus" name="articleStatus">
              <option<?php echo $articleStatus ? '' : ' selected="selected"'; ?> value=""></option>
<?php
    /** @var \BackedEnum $status */
    foreach (ArticleStatus::getAll() as $status) {
        $selected = $status === $articleStatus;
?>
              <option<?php echo $selected ? ' selected="selected"' : ''; ?> value="<?=$status->value?>"><?=$responseData->getLocalValue('articleStatus' . $status->name)?></option>
<?php } ?>
            </select>
          </div>
        </div>
        <a href="#" id="filter-article-button" class="button is-link filter-button"><?=$responseData->getLocalValue('formFilterButton')?></a>
      </div>
    </section>
