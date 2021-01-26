<?php

use uve\core\model\response\HtmlResponseData;
use uve\core\value\Language;

/** @var HtmlResponseData $responseData */

$user = $responseData->getSession()->getUser();

$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

?>
      <h1>Modifica os teus datos</h1>
      <div class="content-main">
        <form id="form-user-account" action="#">
          <input type="hidden" name="userId" id="userId" value="<?=$this->e($user->getId())?>">
          <div class="field">
            <label for="name" class="label">O teu nome:</label>
            <div class="control">
              <input class="input" id="name" name="name" type="text" placeholder="O teu nome" minlength="3" value="<?=$this->e($responseData->getSession()->getUser()->getName())?>" required>
            </div>
            <p class="help"><span class="is-danger">Obrigatorio</span>Mínimo tres letras.</p>
          </div>
          <div class="field">
            <label for="email" class="label">O teu correo electrónico:</label>
            <div class="control">
              <input class="input" id="email" name="email" type="email" placeholder="nome@exemplo.com" value="<?=$this->e($responseData->getSession()->getUser()->getEmail())?>">
            </div>
            <p class="help"><span class="is-danger">Obrigatorio</span></p>
<?php if (!$user->isVerified() && $user->getPreviousEmailAddress()) { ?>
            <p class="warning m-t-3 m-b-0">Recentemente actualizaches o teu correo electrónico e aínda non foi verificado. Por favor revisa a túa bandexa de entrada e verifícao canto antes. Se non recibiras o correo de verificación revisa a caixa do lixo ou <a class="verified-link" data-user-id="<?=$this->e($user->getId())?>" href="#">fai click aquí</a> e enviarémosche outro.</p>
            <p class="m-b-3">Este era o teu anterior correo electrónico: <i><?=$this->e($user->getPreviousEmailAddress())?></i>.</p>
<?php } ?>
          </div>
          <div class="field">
            <label for="languageId" class="label">Idioma:</label>
            <div class="control">
              <select name="languageId" id="languageId">
                <option value="<?=$this->e(Language::GALEGO)?>" <?=$this->e($user->getLanguageId() === Language::GALEGO ? ' selected="selected"' : '')?>>Galego</option>
                <option value="<?=$this->e(Language::ESPANOL)?>" <?=$this->e($user->getLanguageId() === Language::ESPANOL ? ' selected="selected"' : '')?>>Español</option>
                <option value="<?=$this->e(Language::ENGLISH)?>" <?=$this->e($user->getLanguageId() === Language::ENGLISH ? ' selected="selected"' : '')?>>English</option>
              </select>
            </div>
            <p class="help"><span class="is-danger">Obrigatorio</span></p>
          </div>
          <div class="field">
            <label for="timezone" class="label">Hora:</label>
            <div class="control">
              <select name="timezone" id="timezone">
<?php foreach ($timezones as $timezone) { ?>
                <option value="<?=$this->e($timezone)?>" <?=$this->e($user->getTimezone() === $timezone ? ' selected="selected"' : '')?>><?=$this->e($timezone)?></option>
<?php } ?>
              </select>
            </div>
            <p class="help"><span class="is-danger">Obrigatorio</span></p>
          </div>
          <div class="control">
            <button class="button is-success m-b-3" value="Submit">Actualizar</button>
          </div>
        </form>
        <div class="field">
          <div class="control">
            <a href="/account/password"><?=$this->e($responseData->getLocalValue('navChangePassword'))?></a>
          </div>
        </div>
        <div class="field">
          <div class="control">
            <a href="/account/download"><?=$this->e($responseData->getLocalValue('navDownloadAccountData'))?></a>
          </div>
        </div>
        <div class="field m-b-6">
          <div class="control">
            <a class="is-danger" href="/account/delete"><?=$this->e($responseData->getLocalValue('navDeleteAccount'))?></a>
          </div>
        </div>
      </div>
