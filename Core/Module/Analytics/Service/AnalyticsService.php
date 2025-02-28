<?php

namespace Amora\Core\Module\Analytics\Service;

use Amora\App\Module\Analytics\Entity\ReportViewCount;
use Amora\Core\Module\Analytics\Entity\PageView;
use Amora\Core\Module\Analytics\Model\EventValue;
use Amora\Core\Module\Analytics\Value\Parameter;
use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Module\Analytics\Value\Period;
use Amora\Core\Util\DateUtil;
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

            $searchQuery = $request->path === self::SEARCH_ENDPOINT ? $request->getGetParam('q') : null;
            $this->analyticsDataLayer->storeEventRaw(
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
                    searchQuery: $searchQuery ? trim($searchQuery) : null,
                ),
            );
        } catch (Throwable $t) {
            $this->logger->logError('Error logging event: ' . $t->getMessage());
        }
    }

    public function getEventValueForId(Parameter $parameter, int $id): ?EventValue
    {
        return $this->analyticsDataLayer->filterEventValueBy(
            parameter: $parameter,
            id: $id,
        );
    }

    public function getReportViewCount(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        Period $period,
        ?EventType $eventType = null,
        ?Parameter $parameter = null,
        ?int $eventId = null,
        bool $includeVisitorHash = false,
    ): ReportViewCount {
        $aggregateBy = $period->getAggregateBy();

        $pageViews = $this->analyticsDataLayer->calculateCountAggregatedBy(
            from: $from,
            to: $to,
            aggregateBy: $aggregateBy,
            eventType: $eventType,
            parameter: $parameter,
            eventId: $eventId,
            includeVisitorHash: $includeVisitorHash,
        );

        return $this->completeReportViewCount(
            new ReportViewCount(
                from: $from,
                to: $to,
                aggregateBy: $aggregateBy,
                period: $period,
                pageViews: $pageViews,
                eventType: $eventType,
                parameter: $parameter,
            ),
        );
    }

    public function calculateTotalAggregatedBy(
        Parameter $parameter,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        ?EventType $eventType = null,
        ?Parameter $parameterQuery = null,
        ?int $eventId = null,
        int $limit = 25,
    ): array {
        return $this->analyticsDataLayer->calculateTotalAggregatedBy(
            parameter: $parameter,
            from: $from,
            to: $to,
            limit: $limit,
            eventType: $eventType,
            parameterQuery: $parameterQuery,
            eventId: $eventId,
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

        $interval = $report->aggregateBy->getInterval();
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
            parameter: $report->parameter,
        );
    }
}
