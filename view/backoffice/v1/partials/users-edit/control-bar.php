<?php

use uve\core\model\response\HtmlResponseDataAuthorised;
use uve\core\util\DateUtil;
use uve\core\util\StringUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$userToEdit = $responseData->getUserToEdit();
$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

$updatedAtContent = $userToEdit
    ? $responseData->getLocalValue('globalUpdated') . ' <span title="' .
    $this->e(DateUtil::formatUtcDate($userToEdit->getUpdatedAt(), $responseData->getSiteLanguage(), true, true, $responseData->getTimezone())) .
    '">' . $this->e(DateUtil::getElapsedTimeString($userToEdit->getUpdatedAt(), $responseData->getSiteLanguage(), false, true)) . '</span>.'
    : '';

$createdAtContent = $userToEdit
    ? $responseData->getLocalValue('globalCreated') . ' <span title="' .
    $this->e(DateUtil::formatUtcDate($userToEdit->getCreatedAt(), $responseData->getSiteLanguage(), true, true, $responseData->getTimezone())) .
    '">' . $this->e(DateUtil::getElapsedTimeString($userToEdit->getCreatedAt(), $responseData->getSiteLanguage(), false, true)) . '</span>.'
    : '';

$isEnabled = $userToEdit ? $userToEdit->isEnabled() : true;
$random = StringUtil::getRandomString(5);

?>
      <div class="control-bar-wrapper m-b-3 m-t-1">
        <div class="article-control-bar-buttons">
          <input style="width: revert;" type="submit" class="button" value="<?=$userToEdit ? $responseData->getLocalValue('globalUpdate') : $responseData->getLocalValue('globalSave')?>">
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
