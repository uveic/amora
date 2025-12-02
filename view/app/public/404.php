<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;

/** @var HtmlResponseDataAbstract $responseData */

$this->layout('base', ['responseData' => $responseData]);
?>
  <main>
    <section class="section-404">
      <h1><?=$responseData->getLocalValue('globalPageNotFoundTitle')?></h1>
        <?=$responseData->getLocalValue('globalPageNotFoundContent')?>
    </section>
  </main>