<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\User\Model\Session;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\UserHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

if (!$responseData->user) {
    return;
}

$this->layout('base', ['responseData' => $responseData]);

$openSessions = [];
$lastVisitAt = null;
$expiredSessionsCount = 0;

/** @var Session $session */
foreach ($responseData->sessions as $session) {
    if ($session->isExpired()) {
        $expiredSessionsCount++;
    } else {
        $openSessions[] = $session;
        if (!$lastVisitAt || $lastVisitAt < $session->lastVisitedAt) {
            $lastVisitAt = $session->lastVisitedAt;
        }
    }
}

?>
  <main>
    <div id="feedback" class="feedback null"></div>
    <div class="page-header">
      <span class="back-js cursor-pointer"><?=CoreIcons::CARET_LEFT?></span>
      <span class="page-header-expand">
        <span class="icon-one-line"><?=CoreIcons::USER . $responseData->user->getNameOrEmail()?></span>
      </span>
      <div class="links small-screen-hidden">
        <a href="<?=UrlBuilderUtil::buildBackofficeUserEditUrl(language: $responseData->siteLanguage, userId:  $responseData->user->id)?>"><?=CoreIcons::EDIT?></a>
      </div>
    </div>

    <div class="backoffice-wrapper" data-user-id="<?=$responseData->user->id?>">
      <div class="backoffice-child-outer">
        <div class="backoffice-child-container">
          <div class="card-info-item">
            <span class="title"><?=$responseData->getLocalValue('globalStatus')?>:</span>
            <span class="value gap-1">
<?php if ($responseData->user->id === $responseData->request->session->user->id) {
    echo '              ' .
        UserHtmlGenerator::generateUserStatusHtml(
            status: $responseData->user->status,
            language: $responseData->siteLanguage,
        ) . PHP_EOL;
} else {
    echo '              ' .
        UserHtmlGenerator::generateDynamicUserStatusHtml(
                user: $responseData->user,
                language: $responseData->siteLanguage,
        ) . PHP_EOL;
} ?>
            </span>
          </div>
          <div class="card-info-item">
            <span class="title"><?=$responseData->getLocalValue('globalRole')?>:</span>
            <span class="value">
<?php if ($responseData->user->id === $responseData->request->session->user->id && $responseData->user->role === UserRole::Admin) {
    echo '                ' . $responseData->user->role->asHtml($responseData->siteLanguage) . PHP_EOL;
} else {
    echo UserHtmlGenerator::generateDynamicUserRoleHtml(
        user: $responseData->user,
        language: $responseData->siteLanguage,
        indentation: '                ',
    );
}

?>
            </span>
          </div>
          <div class="card-info-item">
            <span class="title"><?=$responseData->getLocalValue('globalName')?>:</span>
            <span class="value"><?=$responseData->user->name?></span>
          </div>
          <div class="card-info-item">
            <span class="title"><?=$responseData->getLocalValue('globalEmail')?>:</span>
            <span class="value"><?=$responseData->user->email?></span>
          </div>
<?php if ($responseData->user->changeEmailAddressTo) { ?>
          <div class="card-info-item">
            <span class="title"><?=$responseData->getLocalValue('formNewEmailVerificationPending')?>:</span>
            <span class="value"><?=$responseData->user->changeEmailAddressTo?></span>
          </div>
<?php }
if (!$responseData->user->isVerified()) { ?>
          <div class="card-info-item">
            <span class="title"></span>
            <span class="value gap-1">
              <span><?=$responseData->user->journeyStatus->asHtml($responseData->siteLanguage)?></span>
              <a href="#" class="send-verification-email-js no-loader" data-user-id="<?=$responseData->user->id?>" data-verification-type-id="<?=$responseData->user->journeyStatus->getVerificationType()?->value?>"><?=$responseData->getLocalValue('formUserSendEmailAgain')?></a>
          </div>
<?php } ?>
        </div>

        <div class="backoffice-child-container">
          <div class="card-info-header">
            <span class="title"><?=$responseData->getLocalValue('formUserActiveSessions')?></span>
          </div>
<?php
    /** @var Session $session */
    foreach ($openSessions as $session) {
?>
          <div class="card-info-item">
            <span class="value gap-1">
              <span><?=DateUtil::formatDateShort($session->createdAt)?></span>
              <span><?=$session->ip?></span>
              <span><?=$session->browserAndPlatform?></span>
            </span>
          </div>
<?php } ?>
        </div>

      </div>

      <div class="backoffice-child-outer">
        <div class="backoffice-child-container">
          <div class="card-info-item">
            <span class="title"><?=$responseData->getLocalValue('formUserLastVisit')?>:</span>
            <span class="value" title="<?=$lastVisitAt ? DateUtil::formatDateShort(date: $lastVisitAt) : ''?>"><?=$lastVisitAt ? DateUtil::getElapsedTimeString(from: $lastVisitAt, includePrefixAndOrSuffix: true, language: $responseData->siteLanguage) : '-'?></span>
          </div>

          <div class="card-info-item">
            <span class="title"><?=$responseData->getLocalValue('formUserExpiredSessions')?>:</span>
            <span class="value"><?=$expiredSessionsCount?></span>
          </div>

          <div class="card-info-item">
            <span class="title"><?=$responseData->getLocalValue('globalCreated')?>:</span>
            <span class="value"><?=DateUtil::formatDate(date: $responseData->user->createdAt, lang: $responseData->siteLanguage, includeTime: true)?></span>
          </div>

          <div class="card-info-item">
            <span class="title"><?=$responseData->getLocalValue('globalUpdated')?>:</span>
            <span class="value"><?=DateUtil::formatDate(date: $responseData->user->updatedAt, lang: $responseData->siteLanguage, includeTime: true)?></span>
          </div>
        </div>

        <div class="backoffice-child-container">
          <div class="card-info-item">
            <span class="title"><?=$responseData->getLocalValue('globalLanguage')?>:</span>
            <span class="value"><?=$responseData->user->language->getName()?></span>
          </div>
          <div class="card-info-item">
            <span class="title"><?=$responseData->getLocalValue('globalTimezone')?>:</span>
            <span class="value"><?=$responseData->user->timezone->getName()?></span>
          </div>
          <div class="card-info-item">
            <span class="title"><?=$responseData->getLocalValue('globalBio')?></span>
            <span class="value"><?=$responseData->user->bio ?: '-'?></span>
          </div>
        </div>
      </div>
    </div>
  </main>
  <script type="module" src="/js/authorised.js?v=132"></script>
