<?php

use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Module\User\Service\UserService;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseData $responseData */
$siteLogoHtml = $responseData->buildSiteLogoHtml($responseData->siteLanguage, className: 'logo-on-top');

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<head>
<?=$this->insert('../../../app/frontend/public/partials/head', ['responseData' => $responseData])?>
  <link href="/css/shared-base.css?v=000" rel="stylesheet" type="text/css">
  <link href="/css/app/style.css?v=000" rel="stylesheet" type="text/css">
</head>
<body>
<main class="main-split-screen">
  <div id="register-left"></div>
  <div id="register-right">
    <a id="register-close" href="<?=UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage)?>"><?=CoreIcons::CLOSE?></a>
    <form method="POST" id="form-register">
      <div>
        <h1 id="register-title" class="m-b-3"><?=$siteLogoHtml?></h1>
        <h2 id="register-subtitle"><?=$this->e($responseData->getLocalValue('authenticationRegisterSubtitle'))?></h2>
        <p class="light-text-color m-b-3"><?=$responseData->getLocalValue('authenticationRegisterAlreadyLogin')?> <a href="<?=UrlBuilderUtil::buildPublicLoginUrl($responseData->siteLanguage)?>"><?=$responseData->getLocalValue('navSignIn')?></a>.</p>
        <div class="field">
          <div class="control">
            <label for="name" class="null">Name</label>
            <input class="input" id="name" name="name" type="text" placeholder="<?=$responseData->getLocalValue('formPlaceholderUserName')?>" value="" required>
          </div>
        </div>
        <div class="field">
          <p class="control has-icons-left has-icons-right">
            <label for="email" class="null">Name</label>
            <input class="input" id="email" name="email" type="email" placeholder="<?=$responseData->getLocalValue('formPlaceholderEmail')?>">
          </p>
        </div>
        <div class="field">
          <p class="control has-icons-left">
            <label for="password" class="null">Name</label>
            <input class="input" id="password" name="password" type="password" placeholder="<?=$responseData->getLocalValue('formPlaceholderCreatePassword')?>" minlength="<?=$this->e(UserService::USER_PASSWORD_MIN_LENGTH)?>">
          </p>
          <p class="help"><span class="is-danger"></span><?=$responseData->getLocalValue('authenticationRegisterPasswordHelp')?></p>
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
<script type="module" src="/js/login.js?v=000"></script>
</body>
</html>
