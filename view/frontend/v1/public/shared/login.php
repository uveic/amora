<?php

use uve\core\model\response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=$this->e(strtolower($responseData->getSiteLanguage()))?>">
<?=
$this->insert('shared/partials/head', ['responseData' => $responseData])
?>
<body>
<main class="main-split-screen">
  <div id="register-left"></div>
  <div id="register-right">
    <a id="register-close" href="<?=$responseData->getBaseUrlWithLanguage()?>">&#10005;</a>
    <form method="POST" id="form-login">
      <div>
        <h1 id="register-title" class="logo m-b-4"><?=$this->e($responseData->getSiteName())?></h1>
        <h2 id="register-subtitle"><?=$this->e($responseData->getLocalValue('authenticationLoginSubtitle'))?></h2>
        <p class="light-text-color m-b-3"></p>
        <div class="field">
          <p class="control">
            <input class="input" type="email" id="user" name="user" placeholder="<?=$this->e($responseData->getLocalValue('formPlaceholderEmail'))?>" required>
          </p>
        </div>
        <div class="field">
          <p class="control">
            <input class="input" type="password" id="password" name="password" placeholder="<?=$this->e($responseData->getLocalValue('formPlaceholderPassword'))?>" required>
          </p>
        </div>
        <div id="login-failure-message" class="field is-failure null"></div>
        <div class="field">
          <p class="control">
            <input class="button is-success" type="submit" value="<?=$this->e($responseData->getLocalValue('formLoginAction'))?>">
          </p>
        </div>
      </div>
      <p class="register-forgot no-margin"><a href="<?=$responseData->getBaseUrlWithLanguage()?>login/forgot"><?=$this->e($responseData->getLocalValue('authenticationForgotPassword'))?></a></p>
    </form>
  </div>
</main>
<script type="module" src="/js/main.js"></script>
</body>
</html>
