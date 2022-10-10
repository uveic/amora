<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);

?>
  <div id="feedback" class="feedback null"></div>
  <main class="m-t-1 m-r-1 m-b-1 m-l-1">
    <div class="field m-t-0 m-b-0">
      <div id="upload-media">
        <div id="upload-media-info">
          <h1><?=$responseData->getLocalValue('navAdminAnalytics')?></h1>
<?=$this->insert('partials/analytics/chart-bar-day', ['responseData' => $responseData]);?>
<?=$this->insert('partials/analytics/chart-bar-day-js', ['responseData' => $responseData]);?>
        </div>
      </div>
    </div>
  </main>
