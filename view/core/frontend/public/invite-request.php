<?php

use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<?=
$this->insert('../../../app/frontend/public/partials/head', ['responseData' => $responseData])
?>
<body>
<main class="main-split-screen">
  <div id="register-left"></div>
  <div id="register-right">
    <a id="register-close" href="<?=UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage)?>">
      <img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalClose')?>">
    </a>
    <form method="POST" id="form-invite-request">
      <div>
        <div class="div-request-form">
          <h1 id="register-title" class="logo m-b-3"><?=$this->e($responseData->siteName)?></h1>
          <h2 id="register-subtitle"><?=$responseData->getLocalValue('authenticationInviteRequest')?></h2>
          <p class="light-text-color m-b-2"><?=$responseData->getLocalValue('authenticationInviteRequestSubtitle')?></p>
          <div class="field">
            <p class="control">
              <input class="input" type="email" id="email" name="email" placeholder="<?=$this->e($responseData->getLocalValue('formPlaceholderEmail'))?>" required>
            </p>
          </div>
          <div class="field m-t-1">
            <input class="button is-success" type="submit" value="<?=$this->e($responseData->getLocalValue('authenticationInviteRequestFormAction'))?>">
            </p>
          </div>
        </div>
        <div id="login-failure-message" class="is-failure m-t-1 null"></div>
        <div id="request-form-feedback" class="field null">
          <p class="m-b-3"><?=$responseData->getLocalValue('authenticationInviteRequestActionSuccess')?></p>
          <a class="button is-success m-t-3" href="<?=UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage)?>"><?=$this->e($responseData->getLocalValue('authenticationInviteRequestHomeLink'))?></a>
        </div>
      </div>
    </form>
  </div>
</main>
<script type="module" src="/js/login.js"></script>
</body>
</html>
