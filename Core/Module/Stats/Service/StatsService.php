<?php

namespace Amora\Core\Module\Stats\Service;

use DateTimeImmutable;
use Throwable;
use Amora\Core\Core;
use Amora\Core\Util\Logger;
use Amora\Core\Entity\Request;
use Amora\Core\Module\Stats\Datalayer\StatsDataLayer;
use Amora\Core\Module\Stats\Model\EventRaw;

class StatsService
{
    public function __construct(
        private readonly Logger $logger,
        private readonly StatsDataLayer $statsDataLayer,
    ) {}

    public function logEvent(Request $request): void
    {
        try {
            if (!Core::getConfig()->isStatsLoggingEnabled) {
                return;
            }

            $this->statsDataLayer->storeEvent(
                new EventRaw(
                    id: null,
                    userId: $request->session?->user->id,
                    sessionId: $request->session?->sessionId,
                    createdAt: new DateTimeImmutable(),
                    url: substr($request->getPath(), 0, 2000),
                    referrer: $request->referrer ? substr($request->referrer, 0, 2000) : null,
                    ip: $request->sourceIp,
                    userAgent: $request->userAgent ? substr($request->userAgent, 0, 255) : null,
                    clientLanguage: $request->clientLanguage ? substr($request->clientLanguage, 0, 255) : null,
                )
            );
        } catch (Throwable $t) {
            $this->logger->logError('Error logging event: ' . $t->getMessage());
        }
    }

    public function getEntriesFromQueue(): array
    {
        $this->logger->logInfo('Releasing locks...');
        $this->statsDataLayer->releaseQueueLocksIfNeeded();

        $this->logger->logInfo('Checking for locked transmissions...');
        $lockedTransmissions = $this->statsDataLayer->getNumberOfLockedTransmissions();
        if ($lockedTransmissions) {
            $this->logger->logInfo(
                'There are (' . $lockedTransmissions . ') transmission(s) locked. Aborting...'
            );

            return [];
        }

        $this->logger->logInfo('Generating unique lock ID...');
        $lockId = $this->statsDataLayer->getUniqueLockId();

        $this->logger->logInfo('Locking entries...');
        $res = $this->statsDataLayer->lockQueueEntries(lockId: $lockId, qty: 5000);
        if (empty($res)) {
            $this->logger->logError('Error locking entries. Aborting...');
            return [];
        }

        $this->logger->logInfo('Getting entries to process...');
        return $this->statsDataLayer->getEntriesFromQueue($lockId);
    }
}
