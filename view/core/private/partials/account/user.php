<?php

use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */

$user = $responseData->request->session->user;

$timezones = DateTimeZone::listIdentifiers();
$languages = Core::getEnabledSiteLanguages();

?>
      <h1 class="m-t-0"><?=$responseData->getLocalValue('formYourAccount')?></h1>
      <div class="content-main">
        <form action="#" method="post" id="form-user-account-update">
          <input type="hidden" name="userId" id="userId" value="<?=$user->id?>">
          <div class="field">
            <label for="name" class="label"><?=$responseData->getLocalValue('formPlaceholderUserName')?>:</label>
            <div class="control">
              <input class="input" id="name" name="name" type="text" placeholder="<?=$responseData->getLocalValue('formPlaceholderUserName')?>" minlength="3" value="<?=$responseData->request->session->user->name?>" required>
            </div>
            <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span><?=$responseData->getLocalValue('formPlaceholderUserHelp')?></p>
          </div>
          <div class="field">
            <label for="email" class="label"><?=$responseData->getLocalValue('formEmail')?>:</label>
            <div class="control">
              <input class="input" id="email" name="email" type="email" placeholder="<?=$responseData->getLocalValue('formPlaceholderEmail')?>" value="<?=$responseData->request->session->user->email?>">
            </div>
            <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
<?php if ($user->changeEmailAddressTo) { ?>
            <p class="warning m-t-3 m-b-3"><?=sprintf($responseData->getLocalValue('formEmailUpdateWarning'), '<i>' . $user->changeEmailAddressTo . '</i>')?></p>
<?php } ?>
          </div>
          <div class="field<?=count($languages) > 1 ? '' :  ' null'?>">
            <label for="languageIsoCode" class="label"><?=$responseData->getLocalValue('globalLanguage')?>:</label>
            <div class="control">
              <select name="languageIsoCode" id="languageIsoCode">
<?php
    /** @var BackedEnum $language */
    foreach ($languages as $language) {
        echo '                <option value="' . $language->value . '"' . ($user->language === $language ? ' selected="selected"' : '') . '>' . $language->getName() . '</option>' . PHP_EOL;
    }
?>
              </select>
            </div>
            <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
          </div>
          <div class="field null">
            <label for="timezone" class="label"><?=$responseData->getLocalValue('formTimezone')?>:</label>
            <div class="control">
              <select name="timezone" id="timezone">
<?php foreach ($timezones as $timezone) { ?>
                <option value="<?=$timezone?>" <?=$user->timezone->getName() === $timezone ? ' selected="selected"' : ''?>><?=$timezone?></option>
<?php } ?>
              </select>
            </div>
            <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
          </div>
          <div class="control">
            <input type="submit" class="button is-success m-b-3" value="<?=$responseData->getLocalValue('globalUpdate')?>">
          </div>
        </form>
        <div class="field">
          <div class="control">
            <a href="<?=UrlBuilderUtil::buildAuthorisedAccountPasswordUrl($responseData->siteLanguage)?>"><?=$responseData->getLocalValue('navChangePassword')?></a>
          </div>
        </div>
      </div>
