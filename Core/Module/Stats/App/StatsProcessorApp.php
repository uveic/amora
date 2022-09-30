<?php

namespace Amora\App\Module\Stats\App;

use Amora\Core\App\App;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Module\Stats\Model\EventRaw;
use Amora\Core\Module\Stats\Service\StatsService;
use Amora\Core\Module\Stats\StatsCore;
use Amora\Core\Util\Logger;

class StatsProcessorApp extends App
{
    public function __construct(
        Logger $logger,
        private readonly StatsService $statsService,
    ) {
        parent::__construct(
            logger: $logger,
            appName: 'Stats Processor',
            lockMaxTimeSinceLastSyncSeconds: 300, // 5 minutes
            isPersistent: false,
        );
    }

    public function run() {
        $this->execute(function () {
            $timeBefore = microtime(true);

            $entries = $this->statsService->getEntriesFromQueue();

            /** @var EventRaw $entry */
            foreach ($entries as $entry) {
                $res = StatsCore::getDb()->withTransaction(
                    function() use ($entry) {
                        $res = $this->processRawEvent($entry);

                        return new Feedback($res);
                    }
                );

                if (!$res->isSuccess) {
                    $this->log('Something went wrong. Aborting...', true);
                    exit;
                }
            }

            $timeAfter = microtime(true);
            $diffMicroseconds = $timeAfter - $timeBefore;
            $totalEntries = count($entries);
            $averageTime = $totalEntries ? round($diffMicroseconds / $totalEntries, 3) : 0;

            $this->log($totalEntries . ' entries processed.');
            $this->log('Average entry process time: ' . $averageTime . ' seconds.');
        });
    }

    private function processRawEvent(EventRaw $event): bool
    {
        return true;
    }
}
