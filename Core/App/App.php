<?php

namespace Amora\Core\App;

use Amora\Core\Core;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;
use Throwable;
use Amora\Core\Util\Logger;

abstract class App
{
    private readonly LockManager $lockManager;
    private readonly int $persistentAppStartedAtTimestamp;
    public readonly string $logPrefix;

    public function __construct(
        protected readonly Logger $logger,
        public readonly string $appName,
        private readonly int $appFrequencySeconds = 5,
        int $lockMaxTimeSinceLastSyncSeconds = 30,
        private readonly bool $isPersistent = false,
        private readonly bool $isLoggingEnabled = true,
        private readonly bool $forceRestartOfPersistentAppOnceADay = true,
    ) {
        if (empty($appName)) {
            $this->log('Empty App name. Aborting...', true);
            exit(1);
        }

        $this->lockManager = new LockManager(
            logger: $this->logger,
            lockName: $this->appName,
            secondsToRemoveLock: $lockMaxTimeSinceLastSyncSeconds,
        );

        $this->logPrefix = $this->appName . ' ::: ';
        $this->persistentAppStartedAtTimestamp = time();
    }

    public function execute(callable $f): void
    {
        $this->log('Starting App...');

        if ($this->lockManager->isRunning()) {
            $this->log(
                'Still running. Lock will be removed in ' .
                $this->lockManager->getSecondsToRemoveLock() .
                ' seconds. Aborting...'
            );
            exit(0);
        }

        $this->lockManager->setLock();

        try {
            $this->isPersistent ? $this->triggerApp($f) : $this->runApp($f);
        } catch (Throwable $t) {
            $this->logger->logException($t);
            exit(1);
        }

        $this->lockManager->removeLock();

        $this->logger->logInfo(
            $this->logPrefix . 'Done. Total time: ' . $this->logger->getTotalTime() . 's'
        );
    }

    private function triggerApp(callable $f): void
    {
        while (true) {
            $this->lockManager->updateLock();

            try {
                $this->runApp($f);
            } catch (Throwable $t) {
                $this->logger->logException($t);
                exit(1);
            }

            sleep($this->appFrequencySeconds);
        }
    }

    private function runApp(callable $f): void
    {
        if ($this->isPersistent && $this->forceRestartOfPersistentAppOnceADay) {
            $now = new DateTimeImmutable();
            $twoAM = DateUtil::convertStringToDateTimeImmutable($now->format('Y-m-d 02:00:00'));
            $sixAM = DateUtil::convertStringToDateTimeImmutable($now->format('Y-m-d 06:00:00'));

            if (
                $now > $twoAM &&
                $now < $sixAM &&
                $now->getTimestamp() - $this->persistentAppStartedAtTimestamp > 86400 // 24 hours
            ) {
                $this->lockManager->removeLock();
                exit(1);
            }
        }

        $this->log('Running...');
        $f();

        $usedMiB = memory_get_usage() / 1024 / 1024;
        $this->log('Memory: ' . number_format($usedMiB, 3) . ' MiB');
        unset($usedMiB);
    }

    protected function log(string $message, bool $isError = false): void
    {
        if (!$this->isLoggingEnabled && !Core::isRunningInLiveEnv()) {
            return;
        }

        if ($isError) {
            $this->logger->logError($this->logPrefix . $message);
            return;
        }

        $this->logger->logInfo($this->logPrefix . $message);
    }
}
