<?php

use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */
$siteLogoHtml = $responseData->buildSiteLogoHtml();

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<head>
<?=$this->insert('../../../app/frontend/public/partials/head', ['responseData' => $responseData])?>
  <link href="/css/style-003.css" rel="stylesheet" type="text/css">
  <link href="/css/app/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<main class="main-split-screen">
  <div id="register-left"></div>
  <div id="register-right">
    <a id="register-close" href="<?=UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage)?>">
      <img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalClose')?>">
    </a>
    <form method="POST" id="form-login-forgot">
      <div>
        <h1 id="register-title" class="logo m-b-4"><?=$siteLogoHtml?></h1>
        <h2 id="register-subtitle"><?=$this->e($responseData->getLocalValue('authenticationForgotPassword'))?></h2>
        <div id="register-form">
          <p class="light-text-color m-b-3"><?=$this->e($responseData->getLocalValue('authenticationForgotPasswordSubtitle'))?></p>
          <div class="field">
            <p class="control">
              <input class="input" type="email" id="email" name="email" placeholder="<?=$this->e($responseData->getLocalValue('formPlaceholderEmail'))?>" required>
            </p>
          </div>
          <div class="field">
            <p class="control">
              <input class="button is-success" type="submit" value="<?=$this->e($responseData->getLocalValue('authenticationForgotPasswordAction'))?>">
            </p>
          </div>
        </div>
      </div>
      <div id="login-failure-message" class="is-failure m-t-1 null"></div>
      <div id="register-back-login" class="field null">
        <p class="m-b-3"><?=$responseData->getLocalValue('authenticationForgotPasswordActionSuccess')?></p>
        <a class="button is-success" href="<?=UrlBuilderUtil::buildPublicLoginUrl($responseData->siteLanguage)?>"><?=$this->e($responseData->getLocalValue('authenticationActionHomeLink'))?></a>
      </div>
    </form>
  </div>
</main>
<script type="module" src="/js/login.js"></script>
</body>
</html>
