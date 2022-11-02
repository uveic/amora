<?php

use Amora\App\Module\Analytics\Entity\ReportPageView;
use Amora\Core\Entity\Response\HtmlResponseDataAnalytics;
use Amora\Core\Module\Analytics\Entity\PageViewCount;
use Amora\Core\Module\Analytics\Value\Period;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\AggregateBy;
use Amora\Core\Value\Country;

/** @var HtmlResponseDataAnalytics $responseData */

$this->layout('base', ['responseData' => $responseData]);

$total = StringUtil::formatNumber(
    language: $responseData->siteLanguage,
    number: $responseData->reportPageViews->total,
);

/** @var ReportPageView $report */
$report = $responseData->reportPageViews;
$now = new DateTimeImmutable();

$dateRange = $label = match($report->period) {
    Period::Day => DateUtil::formatDate(
        date: $report->from,
        lang: $responseData->siteLanguage,
        includeYear: $now->format('Y') !== $report->from->format('Y'),
        includeMonthYearSeparator: false,
        includeDayMonthSeparator: false,
    ),
    Period::Month => DateUtil::formatDate(
        date: $report->from,
        lang: $responseData->siteLanguage,
        includeDay: false,
        includeWeekDay: false,
        includeMonthYearSeparator: false,
    ),
    Period::Year => $report->from->format('Y'),
};

$isNextDisabled = match($report->period) {
    Period::Day => $now->format('Y-m-d') === $report->from->format('Y-m-d'),
    Period::Month => $now->format('Y-m') === $report->from->format('Y-m'),
    Period::Year => $now->format('Y') === $report->from->format('Y'),
};

$todayUrl = UrlBuilderUtil::buildBackofficeAnalyticsUrl(
    language: $responseData->siteLanguage,
    period: Period::Day,
);

$monthUrl = UrlBuilderUtil::buildBackofficeAnalyticsUrl(
    language: $responseData->siteLanguage,
    period: Period::Month,
);

$monthString = DateUtil::formatDate(
    date: $now,
    lang: $responseData->siteLanguage,
    includeDay: false,
    includeWeekDay: false,
    includeMonthYearSeparator: false,
);

$yearUrl = UrlBuilderUtil::buildBackofficeAnalyticsUrl(
    language: $responseData->siteLanguage,
    period: Period::Year,
);

?>
  <div id="feedback" class="feedback null"></div>
  <h1 class="m-l-1 m-r-1"><?=$responseData->getLocalValue('navAdminAnalytics')?></h1>
  <main class="analytics-wrapper">
    <div class="width-100">
      <div class="analytics-header">
        <h2 class="no-margin"><?=$total?></h2>
        <div class="analytics-controls-wrapper">
          <div class="analytics-controls" data-period="<?=$report->period->value?>" data-date="<?=$report->from->format('Y-m-d')?>">
            <a href="#" class="analytics-controls-previous">
              <img src="/img/svg/caret-left.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalPrevious')?>">
            </a>
<?php if ($isNextDisabled) { ?>
            <p class="analytics-controls-next no-margin">
              <img src="/img/svg/caret-right.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalNext')?>">
            </p>
<?php } else { ?>
            <a href="#" class="analytics-controls-next">
              <img src="/img/svg/caret-right.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalNext')?>">
            </a>
<?php } ?>
          </div>
          <a href="#" class="analytics-controls-more">
            <span><?=$dateRange?></span>
            <img src="/img/svg/caret-down.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalNext')?>">
          </a>
          <div class="analytics-controls-more-options null">
            <a href="<?=$todayUrl?>"><?=$responseData->getLocalValue('analyticsToday')?></a>
            <a href="<?=$monthUrl?>"><?=$monthString?></a>
            <a href="<?=$yearUrl?>"><?=$now->format('Y')?></a>
          </div>
        </div>
      </div>
<?=$this->insert('partials/analytics/chart-bar-day', ['responseData' => $responseData]);?>
    </div>
    <div class="analytics-block">
      <h2><?=$responseData->getLocalValue('analyticsSource')?></h2>
<?php
    /** @var PageViewCount $value */
    foreach ($responseData->sources as $value) { ?>
      <div class="item">
        <span class="break"><?=$value->name ?: '-'?></span>
        <span><?=$value->count?></span>
      </div>
<?php } ?>
    </div>
    <div class="analytics-block">
      <h2><?=$responseData->getLocalValue('analyticsPage')?></h2>
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
      <h2><?=$responseData->getLocalValue('analyticsBrowser')?></h2>
<?php
    /** @var PageViewCount $value */
    foreach ($responseData->browsers as $value) {
?>
      <div class="item">
        <span><?=$value->name ?: '-'?></span>
        <span><?=$value->count?></span>
      </div>
<?php } ?>
    </div>
    <div class="analytics-block">
      <h2><?=$responseData->getLocalValue('analyticsDevice')?></h2>
<?php
    /** @var PageViewCount $value */
    foreach ($responseData->devices as $value) { ?>
      <div class="item">
        <span><?=$value->name ?: '-'?></span>
        <span><?=$value->count?></span>
      </div>
<?php } ?>
    </div>
    <div class="analytics-block">
      <h2><?=$responseData->getLocalValue('analyticsCountry')?></h2>
<?php
    /** @var PageViewCount $value */
    foreach ($responseData->countries as $value) {
        $name = Country::getName($value->name) ?: '-';
?>
      <div class="item">
        <span><?=$name?></span>
        <span><?=$value->count?></span>
      </div>
<?php } ?>
    </div>
    <div class="analytics-block">
      <h2><?=$responseData->getLocalValue('analyticsLanguage')?></h2>
<?php
    /** @var PageViewCount $value */
    foreach ($responseData->languages as $value) { ?>
      <div class="item">
        <span><?=$value->name ?: '-'?></span>
        <span><?=$value->count?></span>
      </div>
<?php } ?>
    </div>
  </main>
<?=$this->insert('partials/analytics/chart-bar-day-js', ['responseData' => $responseData]);?>
  <script type="module" src="/js/analytics-001.js"></script>
