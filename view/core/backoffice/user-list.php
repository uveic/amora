<?php

use Amora\Core\Module\User\Model\Session;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\Helper\UserHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);

$sessionByUserId = [];
/** @var Session $session */
foreach ($responseData->sessions as $session) {
    if (!isset($sessionByUserId[$session->user->id])) {
        $sessionByUserId[$session->user->id] = $session;
    }
}

?>
  <main>
<?=$this->insert('partials/user/filter', ['responseData' => $responseData])?>
    <section class="page-header">
      <span><?=$responseData->getLocalValue('navAdminUsers')?></span>
      <div class="links">
        <a href="#" class="filter-open no-loader"><?=CoreIcons::FUNNEL?></a>
        <a href="<?=UrlBuilderUtil::buildBackofficeUserNewUrl($responseData->siteLanguage)?>"><?=CoreIcons::ADD?></a>
        <a href="<?=UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->siteLanguage)?>"><?=CoreIcons::CLOSE?></a>
      </div>
    </section>
    <div class="backoffice-wrapper">
<?=UserHtmlGenerator::generateUserFilterFilterInfoHtml($responseData)?>
      <div class="table">
<?php
    /** @var User $user */
    foreach ($responseData->users as $user) {
        echo UserHtmlGenerator::generateUserRowHtml(
            language: $responseData->siteLanguage,
            user: $user,
            session: $sessionByUserId[$user->id] ?? null,
            indentation: '          ',
        );
    }
?>
      </div>
    </div>
  </main>
