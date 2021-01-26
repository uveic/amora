<?php

use uve\core\model\response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=$this->e(strtolower($responseData->getSiteLanguage()))?>">
<?= $this->insert('shared/partials/head', ['responseData' => $responseData]) ?>
<body>
<main class="main-split-screen">
  <div id="register-left"></div>
  <div id="register-right">
    <a id="register-close" href="<?=$responseData->getBaseUrlWithLanguage()?>">&#10005;</a>
    <form method="POST" id="form-password-reset">
      <input class="input" type="hidden" id="userId" name="userId" value="<?=$responseData->getForgotPasswordUserId()?>">
      <input class="input" type="hidden" id="verificationHash" name="verificationHash" value="<?=$responseData->getVerificationHash()?>">
      <div>
        <h1 id="register-title" class="m-b-6"><?=$this->e($responseData->getSiteName())?></h1>
        <h2 id="register-subtitle"><?=$responseData->getLocalValue('authenticationPasswordResetSubtitle')?></h2>
        <div id="password-reset-form">
          <p class="light-text-color m-b-3"><?=sprintf($responseData->getLocalValue('authenticationPasswordResetAlreadyLogin'), $responseData->getBaseUrlWithLanguage())?></p>
          <div class="field">
            <p class="control has-icons-left">
              <input class="input" type="password" id="password" name="password" placeholder="<?=$responseData->getLocalValue('formPlaceholderPasswordNew')?>" required>
            </p>
            <p class="help"><?=$responseData->getLocalValue('authenticationRegisterPasswordHelp')?></p>
          </div>
          <div class="field">
            <p class="control has-icons-left">
              <input class="input" type="password" id="passwordConfirmation" name="passwordConfirmation" placeholder="<?=$responseData->getLocalValue('formPlaceholderPasswordConfirmation')?>" required>
            </p>
          </div>
          <div id="login-failure-message" class="field is-failure null"></div>
          <div class="field">
            <p class="control">
              <input class="button is-success" type="submit" value="<?=$responseData->getLocalValue('formPasswordResetAction')?>">
            </p>
          </div>
        </div>
      </div>
      <div id="password-reset-success" class="field null">
        <p class="m-b-3"><?=$responseData->getLocalValue('authenticationPasswordResetActionSuccess')?></p>
        <a class="button is-success" href="<?=$responseData->getBaseUrlWithLanguage()?>login"><?=$responseData->getLocalValue('authenticationActionHomeLink')?></a>
      </div>
    </form>
  </div>
</main>
<script type="module" src="/js/main.js"></script>
</body>
</html>
