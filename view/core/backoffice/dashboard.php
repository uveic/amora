<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;

/** @var HtmlResponseDataAbstract $responseData */

$this->layout('base', ['responseData' => $responseData])
?>
  <main>
    <section>
      <div id="feedback" class="feedback null"></div>
      <div class="m-r-1 m-l-1">
        <h1><?=$this->e($responseData->getLocalValue('navAdministrator'))?></h1>
      </div>
      <div class="content-flex">
<?=$this->insert('partials/dashboard/links', ['responseData' => $responseData])?>
<?=$this->insert('partials/dashboard/shortcuts', ['responseData' => $responseData])?>
      </div>
    </section>
  </main>
