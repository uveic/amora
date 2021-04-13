<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Model\Util\LookupTableBasicValue;

/** @var HtmlResponseDataAuthorised $responseData */

$this->layout('base', ['responseData' => $responseData]);
$userToEdit = $responseData->getUserToEdit();
$timezones = DateTimeZone::listIdentifiers();
$emailHelpCopy = $userToEdit ? '' : $responseData->getLocalValue('formEmailNewUserHelp');
$defaultTimezone = $userToEdit
    ? $userToEdit->getTimezone()
    : $responseData->getSession()->getUser()->getTimezone();
$defaultLanguage = $userToEdit
    ? $userToEdit->getLanguageId()
    : $responseData->getSession()->getUser()->getLanguageId();

?>
  <section>
    <div id="feedback" class="feedback null"></div>
    <section class="page-header">
      <h1><?=($userToEdit ? $responseData->getLocalValue('globalEdit') : $responseData->getLocalValue('globalNew')) . ' ' . $responseData->getLocalValue('globalUser')?></h1>
      <div class="links">
        <a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/users" style="font-size: 1.5rem;margin-right: 1rem;">&#10005;</a>
      </div>
    </section>
    <form action="#" method="post" id="form-user-creation">
<?=$this->insert('partials/users-edit/control-bar', ['responseData' => $responseData])?>
      <div class="content-narrow-width">
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
          <label for="email" class="label"><?=$responseData->getLocalValue('globalPassword')?></label>
          <div class="control">
            <?=$emailHelpCopy?>
          </div>
          <p class="help"></p>
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
                      $selected = $language['id'] === $defaultLanguage;
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
    /** @var LookupTableBasicValue $role */
foreach ($responseData->getUserRoles() as $role) {
        $selected = $userToEdit && $role->getId() == $userToEdit->getRoleId();
?>
                <option <?php echo $selected ? 'selected ' : ''; ?>value="<?=$role->getId()?>"><?=$responseData->getLocalValue('userRole' . $role->getName())?></option>
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
<?php foreach ($timezones as $timezone) {
    $selected = $timezone === $defaultTimezone;
?>
                  <option  <?php echo $selected ? 'selected ' : ''; ?>value="<?=$this->e($timezone)?>" <?=$this->e($userToEdit && $userToEdit->getTimezone() === $timezone ? ' selected="selected"' : '')?>><?=$this->e($timezone)?></option>
<?php } ?>
            </select>
          </div>
          <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
        </div>
      </div>
<?=$this->insert('partials/users-edit/control-bar', ['responseData' => $responseData])?>
    </form>
  </section>
