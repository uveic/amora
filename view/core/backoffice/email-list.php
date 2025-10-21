<?php

use Amora\Core\Module\Mailer\Model\MailerItem;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\Helper\MailerHtmlGenerator;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);

?>
  <main>
<?=$this->insert('partials/shared/modal-display-html', ['responseData' => $responseData])?>
    <div class="page-header">
      <span class="back-js cursor-pointer"><?=CoreIcons::CARET_LEFT?></span>
      <span class="icon-one-line width-10-grow"><?=CoreIcons::ENVELOPE_SIMPLE?><span class="ellipsis"><?=$responseData->getLocalValue('mailerListTitle')?></span></span>
      <div class="links"></div>
    </div>
    <div class="backoffice-wrapper">
      <div class="table">
<?php
    /** @var MailerItem $mailerItem */
    foreach ($responseData->emails as $mailerItem) {
        echo '        ' . MailerHtmlGenerator::generateMailerItemRowHtml($responseData, $mailerItem) . PHP_EOL;
    }
?>
      </div>
    </div>
  </main>
