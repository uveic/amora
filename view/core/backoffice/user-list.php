<?php

use Amora\Core\Module\User\Model\User;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\Helper\UserHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData,])

?>
  <main>
    <section class="page-header">
      <h3><?=$responseData->getLocalValue('navAdminUsers')?></h3>
      <div class="links">
        <a href="<?=UrlBuilderUtil::buildBackofficeNewUserUrl($responseData->siteLanguage)?>" class="button is-link admin-menu-button"><?=$responseData->getLocalValue('globalNew')?></a>
      </div>
    </section>
    <div class="m-t-1 m-r-1 m-b-1 m-l-1">
      <div class="table">
        <div class="table-row header">
           <div class="table-item flex-grow-2"><?=$responseData->getLocalValue('globalName')?></div>
        </div>
<?php
    /** @var User $user */
    foreach ($responseData->users as $user) {
        echo UserHtmlGenerator::generateUserRowHtml($responseData, $user);
    }
?>
    </div>
  </main>