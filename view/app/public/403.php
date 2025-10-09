<?php

/** @var HtmlResponseData $responseData */

use Amora\Core\Entity\Response\HtmlResponseData;

$this->layout('base', ['responseData' => $responseData]);
?>
  <main>
    <section class="section-404">
      <h1><?=$responseData->getLocalValue('globalPageNotAuthorisedTitle')?></h1>
      <?=$responseData->getLocalValue('globalPageNotAuthorisedContent')?>
    </section>
  </main>