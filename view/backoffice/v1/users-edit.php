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
        <h1><?=$this->e($userToEdit ? 'Edit' : 'New')?> User</h1>
        <div class="links">
          <a href="/backoffice/users" style="font-size: 1.5rem;margin-right: 1rem;">&#10005;</a>
        </div>
      </div>
      <div class="form-control-bar-header m-b-3">
        <input style="width: revert;" type="submit" class="button" value="<?=$userToEdit ? 'Update' : 'Save'?>">
        <div style="text-align: right"><?=$updatedAtContent?><br><?=$createdAtContent?></div>
        <div id="isEnabled" data-enabled="<?=$isEnabled ? '1' : ''?>" class="enabled-icon-big <?=$isEnabled ? 'feedback-success' : 'feedback-error' ?>"> <?=$isEnabled ? 'Enabled' : 'Disabled' ?></div>
      </div>
      <div class="content-small-width">
<?php if ($userToEdit) { ?>
        <input id="userId" class="input" name="userId" type="hidden" value="<?=$this->e($userToEdit->getId()); ?>">
<?php } ?>
        <div class="field">
          <label for="name" class="label">Name</label>
          <div class="control">
            <input class="input" id="name" name="name" type="text" placeholder="Your name" minlength="3" value="<?=$this->e($userToEdit ? $userToEdit->getName() : ''); ?>" required>
          </div>
          <p class="help"><span class="is-danger">Obrigatorio</span>Mínimo tres letras.</p>
        </div>
        <div class="field">
          <label for="email" class="label">Email</label>
          <div class="control">
            <input class="input" id="email" name="email" type="email" placeholder="Email address" value="<?=$this->e($userToEdit ? $userToEdit->getEmail() : ''); ?>" required>
          </div>
          <p class="help"><span class="is-danger">Obrigatorio</span></p>
        </div>
        <div class="field">
          <label for="bio" class="label">Bio</label>
          <div class="control">
            <textarea id="bio" name="bio"><?=$this->e($userToEdit ? $userToEdit->getBio() : '')?></textarea>
          </div>
        </div>
        <div class="field">
          <label for="languageId" class="label">Language</label>
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
          <p class="help"><span class="is-danger">Obrigatorio</span></p>
        </div>
        <div class="field">
          <label for="roleId" class="label">Role</label>
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
          <p class="help"><span class="is-danger">Obrigatorio</span></p>
        </div>
        <div class="field">
          <label for="timezone" class="label">Hora</label>
          <div class="control">
            <select name="timezone" id="timezone">
                <?php foreach ($timezones as $timezone) { ?>
                  <option value="<?=$this->e($timezone)?>" <?=$this->e($userToEdit && $userToEdit->getTimezone() === $timezone ? ' selected="selected"' : '')?>><?=$this->e($timezone)?></option>
                <?php } ?>
            </select>
          </div>
          <p class="help"><span class="is-danger">Obrigatorio</span></p>
        </div>
<?php if ($userToEdit) { ?>
        <div class="m-t-6 m-b-6">
          <a href="/backoffice/users/<?=$this->e($userToEdit->getId())?>/delete" class="is-danger">Delete User</a>
        </div>
<?php } ?>
      </div>
      <div class="form-control-bar-header m-t-3 m-b-3">
        <input style="width: revert;" type="submit" class="button" value="<?=$userToEdit ? 'Update' : 'Save'?>">
        <div style="text-align: right"><?=$updatedAtContent?><br><?=$createdAtContent?></div>
        <div id="isEnabled" data-enabled="<?=$isEnabled ? '1' : ''?>" class="enabled-icon-big <?=$isEnabled ? 'feedback-success' : 'feedback-error' ?>"> <?=$isEnabled ? 'Enabled' : 'Disabled' ?></div>
      </div>
    </form>
  </section>
