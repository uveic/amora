<?php

use Amora\Core\Entity\Response\HtmlResponseDataAnalytics;
use Amora\Core\Module\Analytics\Entity\PageViewCount;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;
use Amora\Core\Value\Country;

/** @var HtmlResponseDataAnalytics $responseData */

$this->layout('base', ['responseData' => $responseData]);

$total = StringUtil::formatNumber(
    language: $responseData->siteLanguage,
    number: $responseData->reportPageViews->total,
);

$dateRange = DateUtil::formatDate(
        date: $responseData->reportPageViews->from,
        lang: $responseData->siteLanguage,
    )
    . ' - '
    . DateUtil::formatDate(
        date: $responseData->reportPageViews->to,
        lang: $responseData->siteLanguage,
    );

?>
  <div id="feedback" class="feedback null"></div>
  <h1 class="m-l-1 m-r-1"><?=$responseData->getLocalValue('navAdminAnalytics')?></h1>
  <main class="analytics-wrapper">
    <div style="width: 100%;">
      <div class="analytics-header">
        <h1 class="total no-margin"><?=$total?></h1>
        <h2 class="filter no-margin"><?=$dateRange?></h2>
      </div>
<?=$this->insert('partials/analytics/chart-bar-day', ['responseData' => $responseData]);?>
<?=$this->insert('partials/analytics/chart-bar-day-js', ['responseData' => $responseData]);?>
    </div>
    <div class="analytics-block">
      <h1>Sources</h1>
<?php
    /** @var PageViewCount $value */
    foreach ($responseData->sources as $value) { ?>
      <div class="item">
        <span><?=$value->name?></span>
        <span><?=$value->count?></span>
      </div>
<?php } ?>
    </div>
    <div class="analytics-block">
      <h1>Pages</h1>
<?php
    /** @var PageViewCount $value */
    foreach ($responseData->pages as $value) {
        $name = $value->name;
        if ($name === 'home') {
            $name = '';
        }
?>
      <div class="item">
        <a href="/<?=$name?>">/<?=$name?></a>
        <span><?=$value->count?></span>
      </div>
<?php } ?>
    </div>
    <div class="analytics-block">
      <h1>Countries</h1>
<?php
    /** @var PageViewCount $value */
    foreach ($responseData->countries as $value) {
        $name = Country::getName($value->name);
?>
      <div class="item">
        <span><?=$name?></span>
        <span><?=$value->count?></span>
      </div>
<?php } ?>
    </div>
    <div class="analytics-block">
      <h1>Devices</h1>
<?php
    /** @var PageViewCount $value */
    foreach ($responseData->devices as $value) { ?>
      <div class="item">
        <span><?=$value->name?></span>
        <span><?=$value->count?></span>
      </div>
<?php } ?>
    </div>
  </main>
