<?php

use Amora\App\Module\Analytics\Entity\ReportViewCount;
use Amora\App\Value\Language;
use Amora\Core\Entity\Response\HtmlResponseDataAnalytics;
use Amora\Core\Module\Analytics\Entity\PageViewCount;
use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Module\Analytics\Value\Parameter;
use Amora\Core\Module\Analytics\Value\Period;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAnalytics $responseData */

$this->layout('base', ['responseData' => $responseData]);

$totalPageViews = StringUtil::formatNumber(
    language: $responseData->siteLanguage,
    number: $responseData->reportPageViews->total,
);

$totalVisitors = StringUtil::formatNumber(
    language: $responseData->siteLanguage,
    number: count($responseData->visitors),
);

/** @var ReportViewCount $report */
$report = $responseData->reportPageViews;
$now = new DateTimeImmutable();

$dateRange = $label = match ($report->period) {
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

$isNextDisabled = match ($report->period) {
    Period::Day => $now->format('Y-m-d') === $report->from->format('Y-m-d'),
    Period::Month => $now->format('Y-m') === $report->from->format('Y-m'),
    Period::Year => $now->format('Y') === $report->from->format('Y'),
};

$dateParam = $responseData->request->getGetParam('date');
$itemsCountParam = $responseData->request->getGetParam('itemsCount');

$todayUrl = UrlBuilderUtil::buildBackofficeAnalyticsUrl(
    language: $responseData->siteLanguage,
    period: Period::Day,
    eventType: $responseData->reportPageViews->eventType,
    itemsCount: $itemsCountParam,
);

$monthUrl = UrlBuilderUtil::buildBackofficeAnalyticsUrl(
    language: $responseData->siteLanguage,
    period: Period::Month,
    eventType: $responseData->reportPageViews->eventType,
    itemsCount: $itemsCountParam,
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
    eventType: $responseData->reportPageViews->eventType,
    itemsCount: $itemsCountParam,
);

$eventType = $responseData->reportPageViews->eventType
    ? $responseData->getLocalValue('analyticsEventType' . $responseData->reportPageViews->eventType->name)
    : $responseData->getLocalValue('analyticsEventTypeAll');

$eventTypeAllUrl = UrlBuilderUtil::buildBackofficeAnalyticsUrl(
    language: $responseData->siteLanguage,
    period: $responseData->reportPageViews->period,
    date: $dateParam,
    itemsCount: $itemsCountParam,
);

$eventTypeVisitorUrl = UrlBuilderUtil::buildBackofficeAnalyticsUrl(
    language: $responseData->siteLanguage,
    period: $responseData->reportPageViews->period,
    date: $dateParam,
    eventType: EventType::Visitor,
    itemsCount: $itemsCountParam,
);

$eventTypeUserUrl = UrlBuilderUtil::buildBackofficeAnalyticsUrl(
    language: $responseData->siteLanguage,
    period: $responseData->reportPageViews->period,
    date: $dateParam,
    eventType: EventType::User,
    itemsCount: $itemsCountParam,
);

$eventTypeBotUrl = UrlBuilderUtil::buildBackofficeAnalyticsUrl(
    language: $responseData->siteLanguage,
    period: $responseData->reportPageViews->period,
    date: $dateParam,
    eventType: EventType::Bot,
    itemsCount: $itemsCountParam,
);

$eventTypeApiUrl = UrlBuilderUtil::buildBackofficeAnalyticsUrl(
    language: $responseData->siteLanguage,
    period: $responseData->reportPageViews->period,
    date: $dateParam,
    eventType: EventType::Api,
    itemsCount: $itemsCountParam,
);

$eventTypeCrawlerUrl = UrlBuilderUtil::buildBackofficeAnalyticsUrl(
    language: $responseData->siteLanguage,
    period: $responseData->reportPageViews->period,
    date: $dateParam,
    eventType: EventType::Crawler,
    itemsCount: $itemsCountParam,
);

?>
  <div id="feedback" class="feedback null"></div>
  <main class="analytics-wrapper">
    <div class="width-100">
      <div class="analytics-header">
        <div class="analytics-header-left">
          <h2 class="m-t-0 m-b-0">
            <span class="chart-color-01"><?=$totalPageViews?> <span class="chart-title"><?=$responseData->getLocalValue('analyticsPageViews')?></span></span>
            /
            <span class="chart-color-02"><?=$totalVisitors?> <span class="chart-title"><?=$responseData->getLocalValue('analyticsVisitors')?></span></span>
          </h2>
          <a href="#" class="analytics-controls-event-type analytics-controls-option no-loader">
            <span><?=$eventType?></span>
            <?=CoreIcons::CARET_DOWN?>
          </a>
          <div class="analytics-controls-event-type-options analytics-controls-options null">
            <a href="<?=$eventTypeAllUrl?>"><?=$responseData->getLocalValue('analyticsEventTypeAll')?></a>
            <a href="<?=$eventTypeVisitorUrl?>"><?=$responseData->getLocalValue('analyticsEventType' . EventType::Visitor->name)?></a>
            <a href="<?=$eventTypeUserUrl?>"><?=$responseData->getLocalValue('analyticsEventType' . EventType::User->name)?></a>
            <a href="<?=$eventTypeBotUrl?>"><?=$responseData->getLocalValue('analyticsEventType' . EventType::Bot->name)?></a>
            <a href="<?=$eventTypeApiUrl?>"><?=$responseData->getLocalValue('analyticsEventType' . EventType::Api->name)?></a>
            <a href="<?=$eventTypeCrawlerUrl?>"><?=$responseData->getLocalValue('analyticsEventType' . EventType::Crawler->name)?></a>
          </div>
<?php
    if ($report->parameter && $responseData->parameterEventValue) {
        $value = $responseData->parameterEventValue->value;

        if ($report->parameter === Parameter::Url && $value !== '/') {
            $value = '/' . $responseData->parameterEventValue->value;
        } elseif ($report->parameter === Parameter::Language) {
            $lang = Language::tryFrom($responseData->parameterEventValue->value);
            $value = $lang ? $lang->getName() : $responseData->parameterEventValue->value;
        }
?>
          <div class="one-line-flex">
            <span><?=$value?></span>
            <span class="analytics-close-js"><?=CoreIcons::CLOSE?></span>
          </div>
<?php } ?>
        </div>
        <div class="analytics-controls-wrapper">
          <div class="analytics-controls" data-period="<?=$report->period->getName()?>" data-date="<?=$report->from->format('Y-m-d')?>">
            <a href="#" class="analytics-controls-previous"><?=CoreIcons::CARET_LEFT?></a>
<?php if ($isNextDisabled) { ?>
            <p class="analytics-controls-next no-margin"><?=CoreIcons::CARET_RIGHT?></p>
<?php } else { ?>
            <a href="#" class="analytics-controls-next"><?=CoreIcons::CARET_RIGHT?></a>
<?php } ?>
          </div>
          <a href="#" class="analytics-controls-more analytics-controls-option no-loader">
            <span><?=$dateRange?></span>
            <?=CoreIcons::CARET_DOWN?>
          </a>
          <div class="analytics-controls-more-options analytics-controls-options null">
            <a href="<?=$todayUrl?>"><?=$responseData->getLocalValue('analyticsToday')?></a>
            <a href="<?=$monthUrl?>"><?=$monthString?></a>
            <a href="<?=$yearUrl?>"><?=$now->format('Y')?></a>
          </div>
        </div>
      </div>
<?=$this->insert('partials/analytics/chart-bar-day', ['responseData' => $responseData])?>
    </div>
    <div class="analytics-block">
      <h2><?=$responseData->getLocalValue('analyticsSource')?></h2>
<?php
    /** @var PageViewCount $value */
    foreach ($responseData->sources as $value) { ?>
      <div class="item">
        <span class="break"><?=$value->value ?: '-'?></span>
        <span class="no-break"><?=$value->count?></span>
      </div>
<?php } ?>
    </div>
    <div class="analytics-block">
      <h2><?=$responseData->getLocalValue('analyticsPage')?></h2>
<?php
    /** @var PageViewCount $value */
    foreach ($responseData->pages as $value) {
        $valueString = $value->value;
        if ($valueString === 'home' || $valueString === '/') {
            $valueString = '';
        }
?>
      <div class="item">
        <div class="one-line-flex">
          <a href="/<?=$valueString?>">/<?=$valueString?></a>
          <span class="analytics-event-js" data-parameter-id="<?=Parameter::Url->value?>" data-event-id="<?=$value->id?>"><?=CoreIcons::FUNNEL?></span>
        </div>
        <span class="no-break"><?=$value->count?></span>
      </div>
<?php } ?>
    </div>
    <div class="analytics-block width-30">
      <h2><?=$responseData->getLocalValue('analyticsDevice')?></h2>
<?php
    /** @var PageViewCount $value */
    foreach ($responseData->devices as $value) { ?>
          <div class="item">
            <span class="analytics-event-js" data-parameter-id="<?=Parameter::Platform->value?>" data-event-id="<?=$value->id?>"><?=$value->value ?: '-'?></span>
            <span class="no-break"><?=$value->count?></span>
          </div>
<?php } ?>
    </div>
    <div class="analytics-block width-30">
      <h2><?=$responseData->getLocalValue('analyticsBrowser')?></h2>
<?php
    /** @var PageViewCount $value */
    foreach ($responseData->browsers as $value) {
?>
      <div class="item">
        <span class="analytics-event-js" data-parameter-id="<?=Parameter::Browser->value?>" data-event-id="<?=$value->id?>"><?=$value->value ?: '-'?></span>
        <span class="no-break"><?=$value->count?></span>
      </div>
<?php } ?>
    </div>
    <div class="analytics-block width-30">
      <h2><?=$responseData->getLocalValue('analyticsLanguage')?></h2>
<?php
    /** @var PageViewCount $value */
    foreach ($responseData->languages as $value) { ?>
      <div class="item">
        <span class="analytics-event-js" data-parameter-id="<?=Parameter::Language->value?>" data-event-id="<?=$value->id?>"><?=$value->value ?: '-'?></span>
        <span class="no-break"><?=$value->count?></span>
      </div>
<?php } ?>
    </div>
  </main>
<?=$this->insert('partials/analytics/chart-bar-day-js', ['responseData' => $responseData])?>
  <script type="module" src="/js/analytics.js?v=000"></script>
