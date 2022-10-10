<?php

use Amora\Core\Entity\Response\HtmlResponseDataAnalytics;
use Amora\Core\Module\Analytics\Entity\PageViewCount;

/** @var HtmlResponseDataAnalytics $responseData */

$this->layout('base', ['responseData' => $responseData]);

?>
  <div id="feedback" class="feedback null"></div>
  <main class="m-t-1 m-r-1 m-b-1 m-l-1">
    <div>
      <h1><?=$responseData->getLocalValue('navAdminAnalytics')?></h1>
<?=$this->insert('partials/analytics/chart-bar-day', ['responseData' => $responseData]);?>
<?=$this->insert('partials/analytics/chart-bar-day-js', ['responseData' => $responseData]);?>
    </div>
    <div>
      <h1>Top pages</h1>
<?php
    /** @var PageViewCount $value */
    foreach ($responseData->topPages as $value) { ?>
      <p><?=$value->name?>: <?=$value->count?></p>
<?php } ?>
    </div>
  </main>
