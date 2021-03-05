<?php

use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Value\Language;

/** @var HtmlResponseData $responseData */

$user = $responseData->getSession()->getUser();

$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

?>
      <h1><?=$responseData->getLocalValue('formYourAccount')?></h1>
      <div class="content-main">
        <form id="form-user-account" action="#">
          <input type="hidden" name="userId" id="userId" value="<?=$user->getId()?>">
          <div class="field">
            <label for="name" class="label"><?=$responseData->getLocalValue('formPlaceholderUserName')?>:</label>
            <div class="control">
              <input class="input" id="name" name="name" type="text" placeholder="<?=$responseData->getLocalValue('formPlaceholderUserName')?>" minlength="3" value="<?=$this->e($responseData->getSession()->getUser()->getName())?>" required>
            </div>
            <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span><?=$responseData->getLocalValue('formPlaceholderUserHelp')?></p>
          </div>
          <div class="field">
            <label for="email" class="label"><?=$responseData->getLocalValue('formEmail')?>:</label>
            <div class="control">
              <input class="input" id="email" name="email" type="email" placeholder="<?=$responseData->getLocalValue('formPlaceholderEmail')?>" value="<?=$this->e($responseData->getSession()->getUser()->getEmail())?>">
            </div>
            <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
<?php if ($user->getChangeEmailTo()) { ?>
            <p class="warning m-t-3 m-b-3"><?=sprintf($responseData->getLocalValue('formEmailUpdateWarning'), '<i>' . $user->getChangeEmailTo() . '</i>')?></p>
<?php } ?>
          </div>
          <div class="field">
            <label for="languageId" class="label"><?=$responseData->getLocalValue('globalLanguage')?>:</label>
            <div class="control">
              <select name="languageId" id="languageId">
                <option value="<?=Language::GALEGO?>" <?=$user->getLanguageId() === Language::GALEGO ? ' selected="selected"' : ''?>><?=Language::getNameForId(Language::GALEGO)?></option>
                <option value="<?=Language::ESPANOL?>" <?=$user->getLanguageId() === Language::ESPANOL ? ' selected="selected"' : ''?>><?=Language::getNameForId(Language::ESPANOL)?></option>
                <option value="<?=Language::ENGLISH?>" <?=$user->getLanguageId() === Language::ENGLISH ? ' selected="selected"' : ''?>><?=Language::getNameForId(Language::ENGLISH)?></option>
              </select>
            </div>
            <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
          </div>
          <div class="field">
            <label for="timezone" class="label"><?=$responseData->getLocalValue('formTimezone')?>:</label>
            <div class="control">
              <select name="timezone" id="timezone">
<?php foreach ($timezones as $timezone) { ?>
                <option value="<?=$timezone?>" <?=$user->getTimezone() === $timezone ? ' selected="selected"' : ''?>><?=$timezone?></option>
<?php } ?>
              </select>
            </div>
            <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
          </div>
          <div class="control">
            <button class="button is-success m-b-3" value="Submit"><?=$responseData->getLocalValue('globalUpdate')?></button>
          </div>
        </form>
        <div class="field">
          <div class="control">
            <a href="/account/password"><?=$responseData->getLocalValue('navChangePassword')?></a>
          </div>
        </div>
        <div class="field">
          <div class="control">
            <a href="/account/download"><?=$responseData->getLocalValue('navDownloadAccountData')?></a>
          </div>
        </div>
        <div class="field m-b-6">
          <div class="control">
            <a class="is-danger" href="/account/delete"><?=$responseData->getLocalValue('navDeleteAccount')?></a>
          </div>
        </div>
      </div>
