<?php

use Amora\Core\Module\User\Model\User;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\Helper\UserHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData,])

?>
  <main>
    <section class="page-header">
      <span><?=$responseData->getLocalValue('navAdminUsers')?></span>
      <div class="links">
        <a href="<?=UrlBuilderUtil::buildBackofficeUserNewUrl($responseData->siteLanguage)?>"><?=CoreIcons::ADD?></a>
        <a href="<?=UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->siteLanguage)?>"><?=CoreIcons::CLOSE?></a>
      </div>
    </section>
    <div class="backoffice-wrapper">
      <div class="table">
<?php
    /** @var User $user */
    foreach ($responseData->users as $user) {
        echo UserHtmlGenerator::generateUserRowHtml($responseData, $user);
    }
?>
      </div>
    </div>
  </main>
