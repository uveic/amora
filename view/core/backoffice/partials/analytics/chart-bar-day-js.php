<?php

use Amora\Core\Entity\Response\HtmlResponseDataAnalytics;
use Amora\Core\Module\Analytics\Entity\PageView;
use Amora\Core\Util\DateUtil;

/** @var HtmlResponseDataAnalytics $responseData */

$xAxeTitle = '';
$yAxeTitle = '';
$backgroundColour = '#01769e';
$hoverBackgroundColour = '#01289e';

$labels = [];
$data = [];

/** @var PageView $pageView */
foreach ($responseData->reportPageViews->pageViews as $pageView) {
    $label = DateUtil::formatDate(
        date: $pageView->date,
        lang: $responseData->siteLanguage,
        includeYear: false,
        includeDayMonthSeparator: false,
        shortMonthName: true,
    );
    $labels[] = "'$label'";
    $data[] = $pageView->count;
}

?>
  <script src="/js/lib/chart.min.js"></script>
<script>
  const ctxChartLineShared = document.getElementById('chart-line-canvas').getContext('2d');
  let chartLineSharedResponseData = {};
  const chartLineSharedData = {
    labels: [<?=implode(',', $labels)?>],
    datasets: [{
      label: '<?=$yAxeTitle?>',
      data: [<?=implode(',', $data)?>],
      borderColor: '<?=$backgroundColour?>',
      backgroundColor: '<?=$backgroundColour?>',
      borderWidth: 3,
      tension: 0.4,
      cubicInterpolationMode: 'monotone',
      fill: false,
      pointStyle: 'circle',
      pointRadius: 4,
      pointHoverRadius: 8,
    }]
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
          borderColor: '<?=$backgroundColour?>',
          borderWidth: 2,
        },
        title: {
          display: true,
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
