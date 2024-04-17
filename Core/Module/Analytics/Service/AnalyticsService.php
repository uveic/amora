<?php

namespace Amora\Core\Module\Analytics\Service;

use Amora\App\Module\Analytics\Entity\ReportViewCount;
use Amora\Core\Module\Analytics\Entity\PageView;
use Amora\Core\Module\Analytics\Value\CountDbColumn;
use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Module\Analytics\Value\Period;
use Amora\Core\Util\DateUtil;
use Amora\Core\Value\AggregateBy;
use DateTime;
use DateTimeImmutable;
use Throwable;
use Amora\Core\Core;
use Amora\Core\Util\Logger;
use Amora\Core\Entity\Request;
use Amora\Core\Module\Analytics\DataLayer\AnalyticsDataLayer;
use Amora\Core\Module\Analytics\Model\EventRaw;

readonly class AnalyticsService
{
    const URL_MAX_LENGTH = 255;
    const SEARCH_ENDPOINT = 'papi/search';

    public function __construct(
        private Logger $logger,
        private AnalyticsDataLayer $analyticsDataLayer,
    ) {}

    public function logEvent(Request $request): void
    {
        try {
            if (!Core::getConfig()->isAnalyticsEnabled) {
                return;
            }

            $storedEventRaw = $this->analyticsDataLayer->storeEventRaw(
                new EventRaw(
                    id: null,
                    userId: $request->session?->user->id,
                    sessionId: $request->session?->sessionId,
                    createdAt: new DateTimeImmutable(),
                    url: $request->path ? substr($request->path, 0, self::URL_MAX_LENGTH) : null,
                    referrer: $request->referrer ? substr($request->referrer, 0, self::URL_MAX_LENGTH) : null,
                    ip: $request->sourceIp,
                    userAgent: $request->userAgent ? substr($request->userAgent, 0, 255) : null,
                    clientLanguage: $request->clientLanguage ? substr($request->clientLanguage, 0, 255) : null,
                ),
            );

            if ($request->path === self::SEARCH_ENDPOINT) {
                $searchQuery = $request->getGetParam('q');
                if ($searchQuery) {
                    $this->analyticsDataLayer->storeEventRawSearch(
                        rawId: $storedEventRaw->id,
                        searchQuery: $searchQuery,
                    );
                }
            }

        } catch (Throwable $t) {
            $this->logger->logError('Error logging event: ' . $t->getMessage());
        }
    }

    public function countPageViews(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        Period $period,
        ?EventType $eventType = null,
        ?string $url = null,
        ?string $device = null,
        ?string $browser = null,
        ?string $countryIsoCode = null,
        ?string $languageIsoCode = null,
        ?CountDbColumn $columnName = null,
    ): ReportViewCount {
        $aggregateBy = Period::getAggregateBy($period);
        $pageViews = $this->analyticsDataLayer->countPageViews(
            from: $from,
            to: $to,
            aggregateBy: $aggregateBy,
            eventType: $eventType,
            url: $url,
            device: $device,
            browser: $browser,
            countryIsoCode: $countryIsoCode,
            languageIsoCode: $languageIsoCode,
            columnName: $columnName,
        );

        return $this->completeReportViewCount(
            new ReportViewCount(
                from: $from,
                to: $to,
                aggregateBy: $aggregateBy,
                period: $period,
                pageViews: $pageViews,
                eventType: $eventType,
                url: $url,
                device: $device,
                browser: $browser,
                countryIsoCode: $countryIsoCode,
                languageIsoCode: $languageIsoCode,
            ),
        );
    }

    public function countTop(
        CountDbColumn $columnName,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        ?EventType $eventType = null,
        ?string $url = null,
        ?string $device = null,
        ?string $browser = null,
        ?string $countryIsoCode = null,
        ?string $languageIsoCode = null,
        int $limit = 25,
    ): array {
        return $this->analyticsDataLayer->countTop(
            columnName: $columnName,
            from: $from,
            to: $to,
            limit: $limit,
            eventType: $eventType,
            url: $url,
            device: $device,
            browser: $browser,
            countryIsoCode: $countryIsoCode,
            languageIsoCode: $languageIsoCode,
        );
    }

    private function completeReportViewCount(
        ReportViewCount $report,
    ): ReportViewCount {
        $dateFormat = DateUtil::getPhpAggregateFormat($report->aggregateBy);
        $output = [];
        $total = 0;

        /** @var PageView $pageView */
        foreach ($report->pageViews as $pageView) {
            $output[$pageView->date->format($dateFormat)] = $pageView;
            $total += $pageView->count;
        }

        $interval = AggregateBy::getInterval($report->aggregateBy);
        $from = DateTime::createFromImmutable($report->from);

        do {
            if (!isset($output[$from->format($dateFormat)])) {
                $partialDate = $from->format($dateFormat);
                $output[$partialDate] = new PageView(
                    count: 0,
                    date: DateUtil::convertPartialDateFormatToFullDate(
                        partialDate: $partialDate,
                        aggregatedBy: $report->aggregateBy,
                        roundUp: false,
                    ),
                );
            }

            $from->add($interval);
        } while ($from < $report->to);

        ksort($output);

        return new ReportViewCount(
            from: $report->from,
            to: $report->to,
            aggregateBy: $report->aggregateBy,
            period: $report->period,
            pageViews: $output,
            total: $total,
            eventType: $report->eventType,
            url: $report->url,
            device: $report->device,
            browser: $report->browser,
            countryIsoCode: $report->countryIsoCode,
            languageIsoCode: $report->languageIsoCode,
        );
    }
}
