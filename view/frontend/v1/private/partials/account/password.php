<?php

use uve\core\model\response\HtmlResponseData;
use uve\core\module\user\service\UserService;

/** @var HtmlResponseData $responseData */

$user = $responseData->getSession()->getUser();

?>
      <h1>Cambiar contrasinal</h1>
      <p>Modifica os datos da túa conta.</p>
      <div class="content-main">
        <form id="form-user-account" action="#">
          <input type="hidden" name="userId" id="userId" value="<?=$this->e($user->getId())?>">
          <div class="field">
            <label for="currentPassword" class="label">Contrasinal actual:</label>
            <div class="control">
              <input class="input" id="currentPassword" name="currentPassword" type="password" value="" required>
            </div>
            <p class="help"><span class="is-danger">Obrigatorio</span></p>
          </div>
          <div class="field">
            <label for="newPassword" class="label">Novo contrasinal:</label>
            <div class="control">
              <input class="input" id="newPassword" name="newPassword" type="password" value="" minlength="<?=$this->e(UserService::USER_PASSWORD_MIN_LENGTH)?>" required>
            </div>
            <p class="help"><span class="is-danger">Obrigatorio</span>Lonxitude mínima: <?=$this->e(UserService::USER_PASSWORD_MIN_LENGTH)?> caracteres. Recomenable: letras, números e símbolos.</p>
          </div>
          <div class="field">
            <label for="repeatPassword" class="label">Repetir contrasinal:</label>
            <div class="control">
              <input class="input" id="repeatPassword" name="repeatPassword" type="password" value=""  minlength="<?=$this->e(UserService::USER_PASSWORD_MIN_LENGTH)?>" required>
            </div>
            <p class="help"><span class="is-danger">Obrigatorio</span></p>
          </div>
          <div class="control">
            <button class="button is-success m-b-3" value="Submit">Cambiar contrasinal</button>
          </div>
        </form>
      </div>
