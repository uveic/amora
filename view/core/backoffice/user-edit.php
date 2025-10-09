<?php

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);
$timezones = DateTimeZone::listIdentifiers();

$emailHelpCopy = $responseData->user ? '' : $responseData->getLocalValue('formEmailNewUserHelp');
$defaultTimezone = $responseData->user?->timezone ?? $responseData->request->session->user->timezone;
$defaultLanguage = $responseData->user?->language ?? $responseData->request->session->user->language;

?>
  <main>
    <div id="feedback" class="feedback null"></div>
    <div class="page-header">
      <span class="back-js cursor-pointer"><?=CoreIcons::CARET_LEFT?></span>
      <span><?=$responseData->user ? ($responseData->getLocalValue('globalEdit') . ': ' . $responseData->user->name) : ($responseData->getLocalValue('globalNew') . ' ' .  mb_strtolower($responseData->getLocalValue('globalUser'), 'UTF-8'))?></span>
      <div class="links"></div>
    </div>
    <form action="#" method="post" id="form-user-creation">
      <input id="userId" class="input" name="userId" type="hidden" value="<?=$responseData->user?->id?>">
      <div class="backoffice-wrapper">
        <div class="backoffice-child-outer">
          <div class="backoffice-child-container">
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

          </div>
        </div>

        <div class="backoffice-child-outer">
          <div class="backoffice-child-container">
            <div class="field">
              <label for="bio" class="label"><?=$responseData->getLocalValue('globalBio')?></label>
              <div class="control">
                <textarea id="bio" name="bio"><?=$responseData->user?->bio ?? ''?></textarea>
              </div>
            </div>

            <div class="field m-t-2">
              <label for="languageIsoCode" class="label"><?=$responseData->getLocalValue('globalLanguage')?></label>
              <div class="control">
                <select id="languageIsoCode" name="languageIsoCode">
<?php
    /** @var Language $language */
    foreach (Core::getEnabledSiteLanguages() as $language) {
          $selected = $language === $defaultLanguage;
?>
                  <option<?php echo $selected ? ' selected="selected"' : ''; ?> value="<?=$language->value?>"><?=$language->getName()?></option>
<?php } ?>
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
          </div>
        </div>
        <input type="submit" class="button m-t-2 is-success" value="<?=$responseData->user ? $responseData->getLocalValue('globalUpdate') : $responseData->getLocalValue('globalSave')?>">
      </div>
    </form>
  </main>
