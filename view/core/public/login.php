<?php

use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseData $responseData */
$siteLogoHtml = $responseData->buildSiteLogoHtml($responseData->siteLanguage, className: 'logo-on-top');

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<head>
<?=$this->insert('../../app/public/partials/head', ['responseData' => $responseData])?>
  <link href="/css/shared-base.css?v=000" rel="stylesheet" type="text/css">
  <link href="/css/app/style.css?v=000" rel="stylesheet" type="text/css">
</head>
<body>
<main class="main-split-screen">
  <div id="register-left"></div>
  <div id="register-right">
    <a id="register-close" href="<?=UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage)?>"><?=CoreIcons::CLOSE?></a>
    <form method="POST" id="form-login">
      <div>
        <h1 id="register-title" class="m-t-2 m-b-3"><?=$siteLogoHtml?></h1>
        <h2 id="register-subtitle"><?=$this->e($responseData->getLocalValue('authenticationLoginSubtitle'))?></h2>
        <p class="light-text-color m-b-3"></p>
        <div class="field">
          <p class="control">
            <label for="user" class="null">User</label>
            <input class="input" id="user" name="user" type="email" placeholder="<?=$this->e($responseData->getLocalValue('formPlaceholderEmail'))?>" required>
          </p>
        </div>
        <div class="field">
          <p class="control">
            <label for="password" class="null">Password</label>
            <input class="input" id="password" name="password" type="password" placeholder="<?=$this->e($responseData->getLocalValue('formPlaceholderPassword'))?>" required>
          </p>
        </div>
        <div id="login-failure-message" class="is-failure m-t-1 null"></div>
        <div class="field">
          <p class="control">
            <input class="button is-success" type="submit" value="<?=$this->e($responseData->getLocalValue('formLoginAction'))?>">
          </p>
        </div>
      </div>
      <p class="text-right m-t-1">
        <a href="<?=UrlBuilderUtil::buildPublicLoginForgotUrl($responseData->siteLanguage)?>"><?=$responseData->getLocalValue('authenticationForgotPassword')?></a>
      </p>
    </form>
  </div>
</main>
<script type="module" src="/js/login.js?v=000"></script>
</body>
</html>
