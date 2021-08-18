<?php

namespace Amora\Core\App;

use Throwable;
use Amora\Core\Logger;

abstract class App
{
    private LockManager $lockManager;
    private string $logPrefix;

    public function __construct(
        protected Logger $logger,
        private string $appName,
        private int $appFrequencySeconds = 5,
        int $lockMaxTimeSinceLastSyncSeconds = 30,
        private bool $isPersistent = true,
    ) {
        if (empty($appName)) {
            $this->logger->logError('Empty App name value when trying to run an App. Aborting...');
            exit(1);
        }

        $this->lockManager = new LockManager(
            $this->logger,
            $this->appName,
            $lockMaxTimeSinceLastSyncSeconds
        );

        $this->logPrefix = $this->appName . ' ::: ';
    }

    public function execute(callable $f): void
    {
        $this->logger->logInfo($this->logPrefix . 'Starting App...');

        if ($this->lockManager->isRunning()) {
            $this->logger->logInfo(
                $this->logPrefix .
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

    public function getAppName(): string
    {
        return $this->appName;
    }

    public function getLogPrefix(): string
    {
        return $this->logPrefix;
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
        $this->logger->logInfo($this->logPrefix . 'Running...');
        $f();

        $usedMiB = memory_get_usage(false) / 1024 / 1024;
        $this->logger->logInfo(
            $this->logPrefix . 'Done' .
            ' - Memory: ' . number_format($usedMiB, 3) . ' MiB'
        );
        unset($usedMiB);
    }

    protected function log(string $message, bool $isError = false): void
    {
        if ($isError) {
            $this->logger->logError($this->getLogPrefix() . $message);
            return;
        }

        $this->logger->logInfo($this->getLogPrefix() . $message);
    }
}
