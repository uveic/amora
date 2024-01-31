<?php

use Amora\App\Entity\Response\HtmlAppResponseDataAdmin;
use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Module\Analytics\Value\Period;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlAppResponseDataAdmin $responseData */

$totalPageViewsToday = StringUtil::formatNumber(
    language: $responseData->siteLanguage,
    number: $responseData->dashboardCount->pageViewsToday,
);

$totalVisitorsToday = StringUtil::formatNumber(
    language: $responseData->siteLanguage,
    number: $responseData->dashboardCount->visitorsToday,
);

$analyticsTodayUrl = UrlBuilderUtil::buildBackofficeAnalyticsUrl(
    language: $responseData->siteLanguage,
    period: Period::Day,
    eventType: EventType::Visitor,
);

?>
        <div class="dashboard-count">
          <h3 class="no-margin"><?=$responseData->getLocalValue('navAdminAnalytics')?> <span class="dashboard-title-small">(<?=$responseData->getLocalValue('analyticsToday')?>)</span></h3>
          <div class="dashboard-cards-wrapper">
            <a href="<?=$analyticsTodayUrl?>" class="no-margin">
              <span class="value chart-color-01"><?=$totalPageViewsToday?> <span class="chart-title"><?=$responseData->getLocalValue('analyticsPageViews')?></span></span>
              <span class="p-r-05 p-l-05">/</span>
              <span class="value chart-color-02"><?=$totalVisitorsToday?> <span class="chart-title"><?=$responseData->getLocalValue('analyticsVisitors')?></span></span>
            </a>
          </div>
        </div>
