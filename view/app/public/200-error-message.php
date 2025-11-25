<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;

/** @var HtmlResponseDataAbstract $responseData */

$this->layout('base', ['responseData' => $responseData]);
?>
  <main>
    <section class="section-404">
      <h1><?=$responseData->getLocalValue('globalPageGenericErrorTitle')?></h1>
      <p><?=$responseData->feedback?->message?></p>
      <p><?=$responseData->getLocalValue('globalPageGenericErrorContent')?></p>
    </section>
  </main>