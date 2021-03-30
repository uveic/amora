<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */

$this->layout('base', ['responseData' => $responseData])
?>
  <main>
    <section>
      <div id="feedback" class="feedback null"></div>
      <div class="m-r-1 m-l-1">
        <h1><?=$this->e($responseData->getLocalValue('navDashboard'))?></h1>
      </div
      <div class="content-flex">

      </div>
    </section>
  </main>
