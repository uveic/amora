<?php

use uve\core\model\response\HtmlResponseDataAuthorised;
use uve\core\util\DateUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$this->layout('base', ['responseData' => $responseData]);
$userToEdit = $responseData->getUserToEdit();
$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

$updatedAtContent = $userToEdit
    ? 'Updated <span title="' .
        $this->e(DateUtil::formatUtcDate($userToEdit->getUpdatedAt(), $responseData->getSiteLanguage(), true, true, $responseData->getTimezone())) .
        '">' . $this->e(DateUtil::getElapsedTimeString($userToEdit->getUpdatedAt(), $responseData->getSiteLanguage(), false, true)) . '</span>.'
    : '';

$createdAtContent = $userToEdit
    ? 'Created <span title="' .
    $this->e(DateUtil::formatUtcDate($userToEdit->getCreatedAt(), $responseData->getSiteLanguage(), true, true, $responseData->getTimezone())) .
    '">' . $this->e(DateUtil::getElapsedTimeString($userToEdit->getCreatedAt(), $responseData->getSiteLanguage(), false, true)) . '</span>.'
    : '';

$isEnabled = $userToEdit ? $userToEdit->isEnabled() : true;

?>
  <section>
    <div id="feedback" class="feedback null"></div>
    <form id="form-user" action="#">
      <div class="form-header m-t-1 m-l-1 m-r-1">
        <h1><?=($userToEdit ? $responseData->getLocalValue('globalEdit') : $responseData->getLocalValue('globalNew')) . ' ' . $responseData->getLocalValue('globalUser')?></h1>
        <div class="links">
          <a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/users" style="font-size: 1.5rem;margin-right: 1rem;">&#10005;</a>
        </div>
      </div>
<?=$this->insert('partials/users-edit/control-bar', ['responseData' => $responseData])?>
      <div class="content-small-width">
<?php if ($userToEdit) { ?>
        <input id="userId" class="input" name="userId" type="hidden" value="<?=$this->e($userToEdit->getId()); ?>">
<?php } ?>
        <div class="field">
          <label for="name" class="label"><?=$responseData->getLocalValue('globalName')?></label>
          <div class="control">
            <input class="input" id="name" name="name" type="text" placeholder="<?=$responseData->getLocalValue('formPlaceholderUserName')?>" minlength="3" value="<?=$this->e($userToEdit ? $userToEdit->getName() : ''); ?>" required>
          </div>
          <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span>Mínimo tres letras.</p>
        </div>
        <div class="field">
          <label for="email" class="label"><?=$responseData->getLocalValue('globalEmail')?></label>
          <div class="control">
            <input class="input" id="email" name="email" type="email" placeholder="<?=$responseData->getLocalValue('formPlaceholderEmail')?>" value="<?=$this->e($userToEdit ? $userToEdit->getEmail() : ''); ?>" required>
          </div>
          <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
        </div>
        <div class="field">
          <label for="bio" class="label"><?=$responseData->getLocalValue('globalBio')?></label>
          <div class="control">
            <textarea id="bio" name="bio"><?=$this->e($userToEdit ? $userToEdit->getBio() : '')?></textarea>
          </div>
        </div>
        <div class="field">
          <label for="languageId" class="label"><?=$responseData->getLocalValue('globalLanguage')?></label>
          <div class="control">
            <select id="languageId" name="languageId">
<?php
                  foreach ($responseData->getLanguages() as $language) {
                      $selected = $userToEdit && $language['id'] == $userToEdit->getLanguageId();
?>
              <option <?php echo $selected ? 'selected' : ''; ?> value="<?=$this->e($language['id'])?>"><?=$this->e($language['name'])?></option>
<?php
}
?>
            </select>
          </div>
          <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
        </div>
        <div class="field">
          <label for="roleId" class="label"><?=$responseData->getLocalValue('globalRole')?></label>
          <div class="control">
            <select id="roleId" name="roleId">
<?php
  foreach ($responseData->getUserRoles() as $role) {
      $selected = $userToEdit && $role['id'] == $userToEdit->getRoleId();
?>
                <option <?php echo $selected ? 'selected' : ''; ?> value="<?=$this->e($role['id'])?>"><?=$this->e($role['name'])?></option>
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
                <?php foreach ($timezones as $timezone) { ?>
                  <option value="<?=$this->e($timezone)?>" <?=$this->e($userToEdit && $userToEdit->getTimezone() === $timezone ? ' selected="selected"' : '')?>><?=$this->e($timezone)?></option>
                <?php } ?>
            </select>
          </div>
          <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
        </div>
      </div>
<?=$this->insert('partials/users-edit/control-bar', ['responseData' => $responseData])?>
    </form>
  </section>
