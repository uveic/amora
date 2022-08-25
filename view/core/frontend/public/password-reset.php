<?php

use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */

$titleHtml = $responseData->getLocalValue('authenticationPasswordResetSubtitle');
$subtitleHtml = sprintf(
    $responseData->getLocalValue('authenticationPasswordResetAlreadyLogin'),
    UrlBuilderUtil::buildPublicLoginUrl($responseData->siteLanguage),
);

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<?=$this->insert('../../../app/frontend/public/partials/head', ['responseData' => $responseData]) ?>
<body>
<main class="main-split-screen">
  <div id="register-left"></div>
  <div id="register-right">
    <a id="register-close" href="<?=$responseData->getBaseUrlWithLanguage()?>">
      <img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalClose')?>">
    </a>
    <form method="POST" id="form-password-reset">
      <input class="input" type="hidden" id="userId" name="userId" value="<?=$responseData->passwordUserId?>">
      <input class="input" type="hidden" id="verificationHash" name="verificationHash" value="<?=$responseData->verificationHash?>">
      <input class="input" type="hidden" id="postUrl" name="postUrl" value="<?= UrlBuilderUtil::PUBLIC_API_PASSWORD_RESET?>">
      <div>
        <h1 id="register-title" class="m-b-6"><?=$this->e($responseData->siteName)?></h1>
        <h2 id="register-subtitle"><?=$titleHtml?></h2>
        <div id="password-reset-form">
          <p class="light-text-color m-b-3"><?=$subtitleHtml?></p>
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
          <div id="login-failure-message" class="field is-failure m-t-1 null"></div>
          <div class="field">
            <p class="control">
              <input class="button is-success" type="submit" value="<?=$responseData->getLocalValue('formPasswordResetAction')?>">
            </p>
          </div>
        </div>
      </div>
      <div id="password-reset-success" class="field null">
        <p class="m-b-3"><?=$responseData->getLocalValue('authenticationPasswordResetActionSuccess')?></p>
        <a class="button is-success" href="<?=UrlBuilderUtil::buildPublicLoginUrl($responseData->siteLanguage)?>"><?=$responseData->getLocalValue('authenticationActionHomeLink')?></a>
      </div>
    </form>
  </div>
</main>
<script type="module" src="/js/login.js"></script>
</body>
</html>
