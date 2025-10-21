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
    <form method="POST" id="form-login-forgot" class="form-login-workflow-js">
      <div>
        <h1 id="register-title" class="m-b-3"><?=$siteLogoHtml?></h1>
        <h2 id="register-subtitle"><?=$this->e($responseData->getLocalValue('authenticationForgotPassword'))?></h2>
        <div id="register-form">
          <p class="m-b-3"><?=$this->e($responseData->getLocalValue('authenticationForgotPasswordSubtitle'))?></p>
          <div class="field">
            <p class="control">
              <label for="email" class="null">Email</label>
              <input class="input" id="email" name="email" type="email" placeholder="<?=$this->e($responseData->getLocalValue('formPlaceholderEmail'))?>" required>
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
<script type="module" src="/js/login.js?v=000"></script>
</body>
</html>
