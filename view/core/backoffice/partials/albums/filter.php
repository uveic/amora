<?php

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

$albumLanguageIsoCodeGetParam = $responseData->request->getGetParam('lang');
$albumLanguage = $albumLanguageIsoCodeGetParam && Language::tryFrom($albumLanguageIsoCodeGetParam)
    ? Language::from($albumLanguageIsoCodeGetParam)
    : null;

$albumStatusGetParam = $responseData->request->getGetParam('status');
$albumStatus = !empty($albumStatusGetParam) && AlbumStatus::tryFrom($albumStatusGetParam)
    ? AlbumStatus::from($albumStatusGetParam)
    : null;

$filterClass = $albumStatus || $albumLanguage ? '' : ' null';
?>
    <section class="filter-container<?=$filterClass?>">
      <div class="filter-header">
        <span><?=$responseData->getLocalValue('formFilterTitle')?></span>
        <div class="filter-links">
          <a href="#" class="filter-refresh"><?=CoreIcons::ARROW_COUNTER_CLOCKWISE?></a>
          <a href="#" class="filter-close"><?=CoreIcons::CLOSE?></a>
        </div>
      </div>
      <form action="#" method="post" class="form-filter filter-form-wrapper">

        <div class="field">
          <label for="albumLanguageIsoCode" class="label"><?=$responseData->getLocalValue('globalLanguage')?></label>
          <div class="control">
            <select id="albumLanguageIsoCode" name="albumLanguageIsoCode" data-param-name="lang">
              <option<?php echo $albumLanguage ? '' : ' selected="selected"'; ?> value=""></option>
<?php
    /** @var BackedEnum $language */
    foreach (Core::getEnabledSiteLanguages() as $language) {
        $selected = $language === $albumLanguage;
?>
              <option<?php echo $selected ? ' selected="selected"' : ''; ?> value="<?=$language->value?>"><?=$language->name?></option>
<?php } ?>
            </select>
          </div>
        </div>
        <div class="field">
          <label for="albumStatus" class="label"><?=$responseData->getLocalValue('globalStatus')?></label>
          <div class="control">
            <select id="albumStatus" name="albumStatus" data-param-name="status">
              <option<?php echo $albumStatus ? '' : ' selected="selected"'; ?> value=""></option>
<?php
    /** @var BackedEnum $status */
    foreach (AlbumStatus::getAll() as $status) {
        $selected = $status === $albumStatus;
?>
              <option<?php echo $selected ? ' selected="selected"' : ''; ?> value="<?=$status->value?>"><?=$responseData->getLocalValue('articleStatus' . $status->name)?></option>
<?php } ?>
            </select>
          </div>
        </div>

        <input type="submit" class="button filter-button" value="<?=$responseData->getLocalValue('formFilterButton')?>">
      </form>
    </section>
