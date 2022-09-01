<?php

use Amora\Core\Entity\Response\HtmlResponseDataAuthorised;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$userToEdit = $responseData->getUserToEdit();
$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

$updatedAtContent = '';
$createdAtContent = '';
if ($userToEdit) {
    $updatedAtDate = DateUtil::formatDate(
        date: $userToEdit->updatedAt,
        lang: $responseData->siteLanguage,
        includeTime: true,
    );

    $updatedAtEta = DateUtil::getElapsedTimeString(
        language: $responseData->siteLanguage,
        from: $userToEdit->updatedAt,
        includePrefixAndOrSuffix: true
    );

    $createdAtDate = DateUtil::formatDate(
        date: $userToEdit->createdAt,
        lang: $responseData->siteLanguage,
        includeTime: true,
    );

    $createdAtEta = DateUtil::getElapsedTimeString(
        language: $responseData->siteLanguage,
        from: $userToEdit->createdAt,
        includePrefixAndOrSuffix: true,
    );

    $updatedAtContent = $responseData->getLocalValue('globalUpdated') .
        ' <span title="' . $this->e($updatedAtDate) . '">' . $this->e($updatedAtEta) . '</span>.';

    $createdAtContent = $responseData->getLocalValue('globalCreated') .
        ' <span title="' . $this->e($createdAtDate) . '">' . $this->e($createdAtEta) . '</span>.';
}

$isEnabled = $userToEdit ? $userToEdit->isEnabled : true;

?>
      <div class="control-bar-wrapper">
        <div class="pexego-tools-amora">
          <input type="submit" class="button button-form" value="<?=$userToEdit ? $responseData->getLocalValue('globalUpdate') : $responseData->getLocalValue('globalSave')?>">
        </div>
        <div class="control-bar-creation"><span><?=$updatedAtContent?></span><br><span><?=$createdAtContent?></span></div>
        <input type="checkbox" id="user-status-dd-checkbox" class="dropdown-menu">
        <div class="dropdown-container user-status-container">
          <ul>
            <li><a data-checked="<?=$isEnabled ? '1' : '0'?>" data-value="1" class="dropdown-menu-option user-status-dd-option feedback-success" href="#"><?=$responseData->getLocalValue('globalActivated')?></a></li>
            <li><a data-checked="<?=$isEnabled ? '0' : '1'?>" data-value="0" class="dropdown-menu-option user-status-dd-option background-light-color" href="#"><?=$responseData->getLocalValue('globalDeactivated')?></a></li>
          </ul>
          <label id="user-status-dd-label" for="user-status-dd-checkbox" class="dropdown-menu-label <?=$isEnabled ? 'feedback-success' : 'background-light-color' ?>">
            <span><?=($isEnabled ? $responseData->getLocalValue('globalActivated') : $responseData->getLocalValue('globalDeactivated'))?></span>
            <img class="img-svg no-margin" width="20" height="20" src="/img/svg/caret-down-white.svg" alt="Change">
          </label>
        </div>
      </div>
