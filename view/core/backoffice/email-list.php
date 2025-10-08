<?php

use Amora\Core\Module\Mailer\Model\MailerItem;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\Helper\MailerHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);

?>
  <main>
<?=$this->insert('partials/shared/modal-display-html', ['responseData' => $responseData])?>
    <section class="page-header">
      <span><?=$responseData->getLocalValue('mailerListTitle')?></span>
      <div class="links">
        <a href="<?=UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->siteLanguage)?>"><?=CoreIcons::CLOSE?></a>
      </div>
    </section>
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
