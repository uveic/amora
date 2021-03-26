<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$userToEdit = $responseData->getUserToEdit();
$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

$updatedAtDate = DateUtil::formatUtcDate(
    stringDate: $userToEdit->getUpdatedAt(),
    lang: $responseData->getSiteLanguage(),
    includeTime: true,
    timezone: $responseData->getTimezone()
);

$updatedAtEta = DateUtil::getElapsedTimeString(
    datetime: $userToEdit->getUpdatedAt(),
    language: $responseData->getSiteLanguage(),
    includePrefixAndOrSuffix: true
);

$createdAtDate = DateUtil::formatUtcDate(
    stringDate: $userToEdit->getCreatedAt(),
    lang: $responseData->getSiteLanguage(),
    includeTime: true,
    timezone: $responseData->getTimezone()
);

$createdAtEta = DateUtil::getElapsedTimeString(
    datetime: $userToEdit->getCreatedAt(),
    language: $responseData->getSiteLanguage(),
    includePrefixAndOrSuffix: true
);

$updatedAtContent = $userToEdit
    ? $responseData->getLocalValue('globalUpdated') .
        ' <span title="' . $this->e($updatedAtDate) . '">' . $this->e($updatedAtEta) . '</span>.'
    : '';

$createdAtContent = $userToEdit
    ? $responseData->getLocalValue('globalCreated') .
        ' <span title="' . $this->e($createdAtDate) . '">' . $this->e($createdAtEta) . '</span>.'
    : '';

$isEnabled = $userToEdit ? $userToEdit->isEnabled() : true;
$random = StringUtil::getRandomString(5);

?>
      <div class="control-bar-wrapper m-b-3 m-t-1">
        <div class="control-bar-buttons">
          <button class="user-save-button button m-r-1"><?=$userToEdit ? $responseData->getLocalValue('globalUpdate') : $responseData->getLocalValue('globalSave')?></button>
        </div>
        <div class="control-bar-creation"><span><?=$updatedAtContent?></span><br><span><?=$createdAtContent?></span></div>
        <input type="checkbox" id="dropdown-menu-<?=$random?>" class="dropdown-menu">
        <div class="dropdown-container">
          <ul>
            <li><a data-checked="<?=$isEnabled ? '1' : '0'?>" data-value="1" class="dropdown-menu-option user-enabled-option feedback-success" href="#"><?=$responseData->getLocalValue('globalActivated')?></a></li>
            <li><a data-checked="<?=$isEnabled ? '0' : '1'?>" data-value="0" class="dropdown-menu-option user-enabled-option background-light-color" href="#"><?=$responseData->getLocalValue('globalDeactivated')?></a></li>
          </ul>
          <label for="dropdown-menu-<?=$random?>" class="dropdown-menu-label <?=$isEnabled ? 'feedback-success' : 'background-light-color' ?>">
            <span><?=($isEnabled ? $responseData->getLocalValue('globalActivated') : $responseData->getLocalValue('globalDeactivated'))?></span>
            <img class="img-svg no-margin" width="20" height="20" src="/img/svg/caret-down.svg" alt="Change">
          </label>
        </div>
      </div>
