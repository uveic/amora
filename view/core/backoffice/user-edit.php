<?php

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Module\User\Value\UserStatus;
use Amora\Core\Util\DateUtil;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);
$timezones = DateTimeZone::listIdentifiers();
$emailHelpCopy = $responseData->user ? '' : $responseData->getLocalValue('formEmailNewUserHelp');
$defaultTimezone = $responseData->user?->timezone ?? $responseData->request->session->user->timezone;
$defaultLanguage = $responseData->user?->language ?? $responseData->request->session->user->language;

$updatedAtContent = '';
$createdAtContent = '';
if ($responseData->user) {
    $updatedAtDate = DateUtil::formatDate(
        date: $responseData->user->updatedAt,
        lang: $responseData->siteLanguage,
        includeTime: true,
    );

    $createdAtDate = DateUtil::formatDate(
        date: $responseData->user->createdAt,
        lang: $responseData->siteLanguage,
        includeTime: true,
    );

    $updatedAtContent = '<p><strong>' . $responseData->getLocalValue('globalUpdated') . '</strong>: '
        . $updatedAtDate . '.</p>';

    $createdAtContent = '<p><strong>' . $responseData->getLocalValue('globalCreated') . '</strong>: '
        . $createdAtDate . '.</p>';
}

?>
  <section>
    <div id="feedback" class="feedback null"></div>
<?=$this->insert('partials/users-edit/header', ['responseData' => $responseData])?>
    <form action="#" method="post" id="form-user-creation">
      <div class="content-narrow-width">
<?php if ($responseData->user) { ?>
        <input id="userId" class="input" name="userId" type="hidden" value="<?=$responseData->user->id; ?>">
<?php } ?>
        <div class="field">
          <label for="name" class="label"><?=$responseData->getLocalValue('globalName')?></label>
          <div class="control">
            <input class="input" id="name" name="name" type="text" placeholder="<?=$responseData->getLocalValue('formPlaceholderUserName')?>" minlength="3" value="<?=$responseData->user?->name ?? ''?>" required>
          </div>
          <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span><?=$responseData->getLocalValue('formPlaceholderUserHelp')?></p>
        </div>
        <div class="field">
          <label for="email" class="label"><?=$responseData->getLocalValue('globalEmail')?></label>
          <div class="control">
            <input class="input" id="email" name="email" type="email" placeholder="<?=$responseData->getLocalValue('formPlaceholderEmail')?>" value="<?=$responseData->user?->email ?? ''?>" required>
          </div>
          <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
        </div>
        <div class="field">
          <label for="email" class="label"><?=$responseData->getLocalValue('globalPassword')?></label>
          <div class="control">
            <?=$emailHelpCopy?>
          </div>
          <p class="help"></p>
        </div>
        <div class="field">
          <label for="bio" class="label"><?=$responseData->getLocalValue('globalBio')?></label>
          <div class="control">
            <textarea id="bio" name="bio"><?=$responseData->user?->bio ?? ''?></textarea>
          </div>
        </div>
        <div class="field">
          <label for="languageIsoCode" class="label"><?=$responseData->getLocalValue('globalLanguage')?></label>
          <div class="control">
            <select id="languageIsoCode" name="languageIsoCode">
<?php
    /** @var Language $language */
    foreach (Core::getAllLanguages() as $language) {
          $selected = $language === $defaultLanguage;
?>
              <option<?php echo $selected ? ' selected="selected"' : ''; ?> value="<?=$language->value?>"><?=$language->getName()?></option>
<?php } ?>
            </select>
          </div>
          <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
        </div>
        <div class="field">
          <label for="roleId" class="label"><?=$responseData->getLocalValue('globalRole')?></label>
          <div class="control">
            <select id="roleId" name="roleId">
<?php
    foreach (UserRole::getAll() as $role) {
        $selected = $role === $responseData->user?->role;
?>
              <option<?php echo $selected ? ' selected="selected"' : ''; ?> value="<?=$role->value?>"><?=$responseData->getLocalValue('userRole' . $role->name)?></option>
<?php
    }
?>
            </select>
          </div>
          <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
        </div>
        <div class="field">
          <label for="timezone" class="label"><?=$responseData->getLocalValue('globalTimezone')?></label>
          <div class="control">
            <select name="timezone" id="timezone">
<?php
    foreach ($timezones as $timezone) {
        $selected = $timezone === $defaultTimezone->getName();
?>
              <option<?php echo $selected ? ' selected="selected"' : ''; ?> value="<?=$timezone?>" <?=$responseData->user && $responseData->user->timezone->getName() === $timezone ? ' selected="selected"' : ''?>><?=$timezone?></option>
<?php } ?>
            </select>
          </div>
          <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
        </div>
        <div class="field">
          <label for="userStatusId" class="label"><?=$responseData->getLocalValue('globalStatus')?></label>
          <div class="control">
            <select name="userStatusId" id="userStatusId">
<?php
    /** @var UserStatus $role */
    foreach (UserStatus::getAll() as $status) {
        $selected = $status === $responseData->user?->status;
?>
              <option<?php echo $selected ? ' selected="selected"' : ''; ?> value="<?=$status->value?>"><?=$responseData->getLocalValue('userStatus' . $status->name)?></option>
<?php } ?>
            </select>
          </div>
          <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
        </div>
        <?=$createdAtContent?>
        <?=$updatedAtContent?>
        <div class="field">
          <input type="submit" class="button m-t-2 is-success" value="<?=$responseData->user ? $responseData->getLocalValue('globalUpdate') : $responseData->getLocalValue('globalSave')?>">
        </div>
      </div>
    </form>
  </section>
