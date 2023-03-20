<?php

namespace Amora\Core\Module\Analytics\Service;

use Amora\App\Module\Analytics\Entity\ReportViewCount;
use Amora\Core\Module\Analytics\Entity\PageView;
use Amora\Core\Module\Analytics\Model\EventProcessed;
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

class AnalyticsService
{
    public function __construct(
        private readonly Logger $logger,
        private readonly AnalyticsDataLayer $analyticsDataLayer,
    ) {}

    public function logEvent(Request $request): void
    {
        try {
            if (!Core::getConfig()->isAnalyticsEnabled) {
                return;
            }

            $this->analyticsDataLayer->storeEventRaw(
                new EventRaw(
                    id: null,
                    userId: $request->session?->user->id,
                    sessionId: $request->session?->sessionId,
                    createdAt: new DateTimeImmutable(),
                    url: $request->path ? substr($request->path, 0, 2000) : null,
                    referrer: $request->referrer ? substr($request->referrer, 0, 2000) : null,
                    ip: $request->sourceIp,
                    userAgent: $request->userAgent ? substr($request->userAgent, 0, 255) : null,
                    clientLanguage: $request->clientLanguage ? substr($request->clientLanguage, 0, 255) : null,
                ),
            );
        } catch (Throwable $t) {
            $this->logger->logError('Error logging event: ' . $t->getMessage());
        }
    }

    public function countPageViews(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        Period $period,
        ?EventType $eventType = null,
        ?CountDbColumn $columnName = null,
    ): ReportViewCount {
        $aggregateBy = Period::getAggregateBy($period);
        $pageViews = $this->analyticsDataLayer->countPageViews(
            from: $from,
            to: $to,
            aggregateBy: $aggregateBy,
            eventType: $eventType,
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
            ),
        );
    }

    public function countTop(
        CountDbColumn $columnName,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        ?EventType $eventType = null,
        int $limit = 25,
    ): array {
        return $this->analyticsDataLayer->countTop(
            columnName: $columnName,
            from: $from,
            to: $to,
            limit: $limit,
            eventType: $eventType,
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
        );
    }

    public function storeEventProcessed(EventProcessed $event): ?EventProcessed
    {
        return $this->analyticsDataLayer->storeEventProcessed($event);
    }

    public function markEventAsProcessed(int $rawId): bool
    {
        return $this->analyticsDataLayer->markEventAsProcessed($rawId);
    }

    public function getEntriesFromQueue(): array
    {
        $this->logger->logInfo('Releasing locks...');
        $this->analyticsDataLayer->releaseQueueLocksIfNeeded();

        $this->logger->logInfo('Checking for locked entries...');
        $lockedEntries = $this->analyticsDataLayer->getNumberOfLockedEntries();
        if ($lockedEntries) {
            $this->logger->logInfo('There are (' . $lockedEntries . ') entries(s) locked. Aborting...'
            );

            return [];
        }

        $this->logger->logInfo('Generating unique lock ID...');
        $lockId = $this->analyticsDataLayer->getUniqueLockId();

        $this->logger->logInfo('Locking entries...');
        $res = $this->analyticsDataLayer->lockQueueEntries(lockId: $lockId, qty: 5000);
        if (empty($res)) {
            $this->logger->logError('Error locking entries. Aborting...');
            return [];
        }

        $this->logger->logInfo('Getting entries to process...');
        return $this->analyticsDataLayer->getEntriesFromQueue($lockId);
    }
}
