<?php

use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

$user = $responseData->request->session->user;

?>
      <h1 class="m-t-0"><?=$responseData->getLocalValue('navChangePassword')?></h1>
      <div class="content-main">
        <form action="#" method="post" id="form-user-account-update">
          <input type="hidden" name="userId" id="userId" value="<?=$user->id?>">
          <div class="field">
            <label for="currentPassword" class="label"><?=$responseData->getLocalValue('formPlaceholderPassword')?>:</label>
            <div class="control">
              <input class="input" id="currentPassword" name="currentPassword" autocomplete="current-password" type="password" value="" required>
            </div>
            <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
          </div>
          <div class="field">
            <label for="newPassword" class="label"><?=$responseData->getLocalValue('formPlaceholderPasswordNew')?>:</label>
            <div class="control">
              <input class="input" id="newPassword" name="newPassword" autocomplete="new-password" type="password" value="" minlength="<?=Core::USER_PASSWORD_MIN_LENGTH?>" required>
            </div>
            <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span><?=sprintf($responseData->getLocalValue('authenticationRegisterPasswordHelp'), Core::USER_PASSWORD_MIN_LENGTH)?></p>
          </div>
          <div class="field">
            <label for="repeatPassword" class="label"><?=$responseData->getLocalValue('formPlaceholderPasswordConfirmation')?>:</label>
            <div class="control">
              <input class="input" id="repeatPassword" name="repeatPassword" autocomplete="new-password" type="password" value=""  minlength="<?=Core::USER_PASSWORD_MIN_LENGTH?>" required>
            </div>
            <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
          </div>
          <div class="control">
            <input type="submit" class="button is-success m-b-3" value="<?=$responseData->getLocalValue('formPasswordResetAction')?>">
          </div>
        </form>
      </div>
