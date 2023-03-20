<?php

use Amora\App\Module\Analytics\Entity\ReportViewCount;
use Amora\Core\Entity\Response\HtmlResponseDataAnalytics;
use Amora\Core\Module\Analytics\Entity\PageView;
use Amora\Core\Util\DateUtil;
use Amora\Core\Value\AggregateBy;

/** @var HtmlResponseDataAnalytics $responseData */

$xAxeTitle = '';
$yAxeTitlePageViews = $responseData->getLocalValue('analyticsPageViews');
$yAxeTitleVisitors = $responseData->getLocalValue('analyticsVisitors');
$backgroundColour01 = '#488f31';
$backgroundColour02 = '#de425b';

$labels = [];
$dataPageViews = [];
$dataVisitors = [];

/** @var ReportViewCount $report */
$reportPageViews = $responseData->reportPageViews;

/** @var PageView $pageView */
foreach ($reportPageViews->pageViews as $pageView) {
    $label = match($reportPageViews->aggregateBy) {
        AggregateBy::Hour => $pageView->date->format('H') . ':00',
        AggregateBy::Day => $pageView->date->format('j'),
        AggregateBy::Month => DateUtil::getMonthName(
            month: $pageView->date->format('n'),
            lang: $responseData->siteLanguage,
        ),
        default => DateUtil::formatDate(
            date: $pageView->date,
            lang: $responseData->siteLanguage,
            includeYear: false,
            includeDayMonthSeparator: false,
            shortMonthName: true,
        ),
    };

    $labels[] = "'$label'";
    $dataPageViews[] = $pageView->count;
}


/** @var PageView $pageView */
foreach ($responseData->reportVisitors->pageViews as $pageView) {
    $label = match($responseData->reportVisitors->aggregateBy) {
        AggregateBy::Hour => $pageView->date->format('H') . ':00',
        AggregateBy::Day => $pageView->date->format('j'),
        AggregateBy::Month => DateUtil::getMonthName(
            month: $pageView->date->format('n'),
            lang: $responseData->siteLanguage,
        ),
        default => DateUtil::formatDate(
            date: $pageView->date,
            lang: $responseData->siteLanguage,
            includeYear: false,
            includeDayMonthSeparator: false,
            shortMonthName: true,
        ),
    };

    $dataVisitors[] = $pageView->count;
}

?>
  <script src="/js/lib/chart.min.js"></script>
  <script nonce="<?=$responseData->nonce?>">
    const ctxChartLineShared = document.getElementById('chart-line-canvas').getContext('2d');
    let chartLineSharedResponseData = {};
    const chartLineSharedData = {
      labels: [<?=implode(',', $labels)?>],
      datasets: [
          {
            label: '<?=$yAxeTitlePageViews?>',
            data: [<?=implode(',', $dataPageViews)?>],
            borderColor: '<?=$backgroundColour01?>',
            backgroundColor: '<?=$backgroundColour01?>',
            borderWidth: 3,
            tension: 0.4,
            cubicInterpolationMode: 'monotone',
            fill: false,
            pointStyle: 'circle',
            pointRadius: 4,
            pointHoverRadius: 8,
          },
          {
              label: '<?=$yAxeTitleVisitors?>',
              data: [<?=implode(',', $dataVisitors)?>],
              borderColor: '<?=$backgroundColour02?>',
              backgroundColor: '<?=$backgroundColour02?>',
              borderWidth: 3,
              tension: 0.4,
              cubicInterpolationMode: 'monotone',
              fill: false,
              pointStyle: 'circle',
              pointRadius: 4,
              pointHoverRadius: 8,
          },
      ],
    };
    const chartLineSharedOptions = {
      responsive: true,
      maintainAspectRatio: true,
      animation: false,
      locale: 'es-ES',
      scales: {
        y: {
          beginAtZero: true,
          suggestedMin: 0,
          suggestedMax: null,
          display: true,
          grid: {
            display: true,
            borderWidth: 0,
          },
          title: {
            display: false,
          },
          ticks: {
            autoSkip: true,
            autoSkipPadding: 10,
            maxRotation: 0,
            minRotation: 0,
          }
        },
        x: {
          display: true,
          grid: {
            display: false,
            borderColor: '<?=$backgroundColour01?>',
            borderWidth: 2,
          },
          title: {
            display: false,
            text: '<?=$xAxeTitle?>'
          },
          ticks: {
            maxRotation: 0,
            minRotation: 0,
          }
        }
      },
      plugins: {
        tooltip: {
          enabled: true,
          position: 'nearest',
        },
        legend: {
          display: false,
        },
        title: {
          display: false,
        }
      }
    };
    const chartLineShared = new Chart(ctxChartLineShared, {
      type: 'line',
      data: chartLineSharedData,
      options: chartLineSharedOptions
    });
  </script>
