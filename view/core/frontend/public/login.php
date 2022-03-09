<?php

use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */
$siteLogo = $responseData->getSiteLogoUrl()
    ? '<img src="' . $responseData->getSiteLogoUrl() . '" alt="' . $responseData->siteName . '">'
    : $this->e($responseData->siteName);
?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<?=
$this->insert('partials/head', ['responseData' => $responseData])
?>
<body>
<main class="main-split-screen">
  <div id="register-left"></div>
  <div id="register-right">
    <a id="register-close" href="<?=$responseData->baseUrlWithLanguage?>">
      <img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalClose')?>">
    </a>
    <form method="POST" id="form-login">
      <div>
        <h1 id="register-title" class="logo m-t-2 m-b-3"><?=$siteLogo?></h1>
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
      <p class="text-right no-margin">
        <a href="<?=UrlBuilderUtil::buildPublicLoginForgotUrl($responseData->siteLanguage)?>"><?=$responseData->getLocalValue('authenticationForgotPassword')?></a>
      </p>
    </form>
  </div>
</main>
<script type="module" src="/js/login.js"></script>
</body>
</html>
