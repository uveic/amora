<?php

use Amora\Core\Module\Mailer\Model\MailerItem;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\Helper\MailerHtmlGenerator;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData,])

?>
  <main>
    <section class="page-header">
      <h3><?=$responseData->getLocalValue('navAdminEmails')?></h3>
      <div class="links"></div>
    </section>
    <div class="backoffice-wrapper">
      <div class="table">
        <div class="table-row header">
           <div class="table-item flex-grow-2"><?=$responseData->getLocalValue('mailerListTitle')?></div>
        </div>
<?php
    /** @var MailerItem $mailerItem */
    foreach ($responseData->emails as $mailerItem) {
        echo MailerHtmlGenerator::generateMailerItemRowHtml($responseData, $mailerItem);
    }
?>
      </div>
    </div>
  </main>
