<?php

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Album\Value\AlbumStatus;

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
        <h3><?=$responseData->getLocalValue('formFilterTitle')?></h3>
        <div class="filter-links">
          <a href="#" class="filter-refresh"><img src="/img/svg/arrow-counter-clockwise.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('formFilterClean')?>"></a>
          <a href="#" class="filter-close"><img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalClose')?>"></a>
        </div>
      </div>
      <div class="filter-form-wrapper">

        <div class="field">
          <label for="albumLanguageIsoCode" class="label"><?=$responseData->getLocalValue('globalLanguage')?></label>
          <div class="control">
            <select id="albumLanguageIsoCode" name="albumLanguageIsoCode">
              <option<?php echo $albumLanguage ? '' : ' selected="selected"'; ?> value=""></option>
<?php
    /** @var BackedEnum $language */
    foreach (Core::getAllLanguages() as $language) {
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
            <select id="albumStatus" name="albumStatus">
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
        <a href="#" class="filter-album-button button is-link filter-button"><?=$responseData->getLocalValue('formFilterButton')?></a>
      </div>
    </section>
