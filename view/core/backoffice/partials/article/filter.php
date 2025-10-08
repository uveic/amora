<?php

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

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

$filterClass = $articleStatus || $articleLanguage ? '' : ' null';
?>
    <section class="filter-container<?=$filterClass?>">
      <div class="filter-header">
        <span><?=$responseData->getLocalValue('formFilterTitle')?></span>
        <div class="filter-links">
          <a href="#" class="filter-article-refresh"><?=CoreIcons::ARROW_COUNTER_CLOCKWISE?></a>
          <a href="#" class="filter-close"><?=CoreIcons::CLOSE?></a>
        </div>
      </div>
      <form action="#" method="post" class="form-filter filter-form-wrapper">
        <div class="field">
          <label for="articleType" class="label"><?=$responseData->getLocalValue('formFilterArticleTypeTitle')?></label>
          <div class="control">
            <select id="articleType" name="articleType" data-param-name="atId">
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
            <select id="articleLanguageIsoCode" name="articleLanguageIsoCode" data-param-name="lang">
              <option<?php echo $articleLanguage ? '' : ' selected="selected"'; ?> value=""></option>
<?php
    /** @var \BackedEnum $language */
    foreach (Core::getEnabledSiteLanguages() as $language) {
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
            <select id="articleStatus" name="articleStatus" data-param-name="status">
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
        <input type="submit" class="button is-link filter-button" value="<?=$responseData->getLocalValue('formFilterButton')?>">
      </form>
    </section>
