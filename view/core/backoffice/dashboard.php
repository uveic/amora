<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;

/** @var HtmlResponseDataAbstract $responseData */

$this->layout('base', ['responseData' => $responseData])
?>
  <main>
    <section>
      <div id="feedback" class="feedback null"></div>
      <div class="backoffice-wrapper">
<?=$this->insert('partials/dashboard/links', ['responseData' => $responseData])?>
<?=$this->insert('partials/dashboard/shortcuts', ['responseData' => $responseData])?>
      </div>
    </section>
  </main>
