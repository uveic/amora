<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\Language;

/** @var HtmlResponseDataAuthorised $responseData */

$this->layout('base', ['responseData' => $responseData]);
$userToEdit = $responseData->getUserToEdit();
$timezones = DateTimeZone::listIdentifiers();
$emailHelpCopy = $userToEdit ? '' : $responseData->getLocalValue('formEmailNewUserHelp');
$defaultTimezone = $userToEdit
    ? $userToEdit->timezone
    : $responseData->request->session->user->timezone;
$defaultLanguage = $userToEdit
    ? $userToEdit->languageId
    : $responseData->request->session->user->languageId;

?>
  <section>
    <div id="feedback" class="feedback null"></div>
<?=$this->insert('partials/users-edit/header', ['responseData' => $responseData])?>
    <form action="#" method="post" id="form-user-creation">
<?=$this->insert('partials/users-edit/control-bar', ['responseData' => $responseData])?>
      <div class="content-narrow-width">
<?php if ($userToEdit) { ?>
        <input id="userId" class="input" name="userId" type="hidden" value="<?=$userToEdit->id; ?>">
<?php } ?>
        <div class="field">
          <label for="name" class="label"><?=$responseData->getLocalValue('globalName')?></label>
          <div class="control">
            <input class="input" id="name" name="name" type="text" placeholder="<?=$responseData->getLocalValue('formPlaceholderUserName')?>" minlength="3" value="<?=$userToEdit ? htmlspecialchars($userToEdit->name) : ''; ?>" required>
          </div>
          <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span><?=$responseData->getLocalValue('formPlaceholderUserHelp')?></p>
        </div>
        <div class="field">
          <label for="email" class="label"><?=$responseData->getLocalValue('globalEmail')?></label>
          <div class="control">
            <input class="input" id="email" name="email" type="email" placeholder="<?=$responseData->getLocalValue('formPlaceholderEmail')?>" value="<?=$userToEdit ? htmlspecialchars($userToEdit->email) : ''?>" required>
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
            <textarea id="bio" name="bio"><?=$userToEdit ? $userToEdit->bio : ''?></textarea>
          </div>
        </div>
        <div class="field">
          <label for="languageId" class="label"><?=$responseData->getLocalValue('globalLanguage')?></label>
          <div class="control">
            <select id="languageId" name="languageId">
<?php
                  foreach (Language::getAll() as $language) {
                      $selected = $language['id'] === $defaultLanguage;
?>
              <option <?php echo $selected ? 'selected' : ''; ?> value="<?=$language['id']?>"><?=$language['name']?></option>
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
/** @var \BackedEnum $role */
foreach (UserRole::getAll() as $role) {
        $selected = $userToEdit && $role == $userToEdit->role;
?>
                <option <?php echo $selected ? 'selected ' : ''; ?>value="<?=$role->value?>"><?=$responseData->getLocalValue('userRole' . $role->name)?></option>
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
                  <option  <?php echo $selected ? 'selected ' : ''; ?>value="<?=$timezone?>" <?=$userToEdit && $userToEdit->timezone->getName() === $timezone ? ' selected="selected"' : ''?>><?=$timezone?></option>
<?php } ?>
            </select>
          </div>
          <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
        </div>
      </div>
<?=$this->insert('partials/users-edit/control-bar', ['responseData' => $responseData])?>
    </form>
  </section>
