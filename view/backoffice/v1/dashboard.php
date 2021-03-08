<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */

$this->layout('base', ['responseData' => $responseData])
?>
  <main>
    <section class="content">
      <div id="feedback" class="feedback null"></div>
      <h1 class="h1-flex"><?=$this->e($responseData->getLocalValue('navAdminDashboard'))?></h1>
      <div class="content-flex">
        <?=$this->insert('partials/dashboard/links', ['responseData' => $responseData])?>
        <?=$this->insert('partials/dashboard/homepage', ['responseData' => $responseData])?>
      </div>
    </section>
  </main>
