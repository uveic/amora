<?php

use uve\core\model\response\HtmlResponseDataAuthorised;
use uve\core\util\DateUtil;

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

?>
      <div class="form-control-bar-header m-b-3">
        <input style="width: revert;" type="submit" class="button" value="<?=$userToEdit ? $responseData->getLocalValue('globalUpdate') : $responseData->getLocalValue('globalSave')?>">
        <div style="text-align: right"><?=$updatedAtContent?><br><?=$createdAtContent?></div>
        <div id="isEnabled" data-enabled="<?=$isEnabled ? '1' : ''?>" class="enabled-icon-big <?=$isEnabled ? 'feedback-success' : 'feedback-error' ?>"> <?=$isEnabled ? 'Enabled' : 'Disabled' ?></div>
      </div>
