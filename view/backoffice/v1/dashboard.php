<?php

use uve\core\model\response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */

$this->layout('base', ['responseData' => $responseData])
?>
  <main>
    <section class="content">
      <div id="feedback" class="feedback null"></div>
      <h1 class="h1-flex"><?=$this->e($responseData->getLocalValue('navDashboard'))?></h1>
      <div class="content-flex">
        <?=$this->insert('partials/dashboard/links', ['responseData' => $responseData])?>
      </div>
    </section>
  </main>
