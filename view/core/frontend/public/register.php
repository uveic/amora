<?php

use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<?=$this->insert('../../../app/frontend/public/partials/head', ['responseData' => $responseData]) ?>
<body>
<main class="main-split-screen">
  <div id="register-left"></div>
  <div id="register-right">
    <a id="register-close" href="<?=UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage)?>">
      <img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalClose')?>">
    </a>
    <form method="POST" id="form-register">
      <div>
        <h1 id="register-title" class="logo m-b-4"><?=$this->e($responseData->siteName)?></h1>
        <h2 id="register-subtitle"><?=$this->e($responseData->getLocalValue('authenticationRegisterSubtitle'))?></h2>
        <p class="light-text-color m-b-3"><?=$responseData->getLocalValue('authenticationRegisterAlreadyLogin')?> <a href="<?=UrlBuilderUtil::buildPublicLoginUrl($responseData->siteLanguage)?>"><?=$responseData->getLocalValue('navSignIn')?></a>.</p>
        <div class="field">
          <div class="control">
            <input class="input" name="name" type="text" placeholder="<?=$responseData->getLocalValue('formPlaceholderUserName')?>" value="" required>
          </div>
        </div>
        <div class="field">
          <p class="control has-icons-left has-icons-right">
            <input class="input" type="email" id="email" name="email" placeholder="<?=$responseData->getLocalValue('formPlaceholderEmail')?>">
          </p>
        </div>
        <div class="field">
          <p class="control has-icons-left">
            <input class="input" type="password" id="password" name="password" placeholder="<?=$responseData->getLocalValue('formPlaceholderCreatePassword')?>" minlength="<?=$this->e(UserService::USER_PASSWORD_MIN_LENGTH)?>">
          </p>
          <p class="help"><?=$responseData->getLocalValue('authenticationRegisterPasswordHelp')?></p>
        </div>
        <div id="login-failure-message" class="is-failure m-t-1 null"></div>
        <div id="register-terms" class="field">
          <p class="no-margin"><?=$responseData->getLocalValue('authenticationRegisterTOS')?></p>
        </div>
        <div class="field">
          <p class="control">
            <input class="button is-success" type="submit" value="<?=$this->e($responseData->getLocalValue('navSignUp'))?>">
          </p>
        </div>
      </div>
    </form>
  </div>
</main>
<script type="module" src="/js/login.js"></script>
</body>
</html>
