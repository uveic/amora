<?php

namespace Amora\Core\App;

use Amora\Core\Logger;
use Amora\Core\Util\StringUtil;

class LockManager
{
    private Logger $logger;
    private string $lockFolder;
    private string $lockType;
    private string $lockName;
    private int $secondsToRemoveLock;
    private string $logPrefix;

    public function __construct(
        Logger $logger,
        string $lockName,
        int $secondsToRemoveLock = 0,
        string $lockFolder = '/tmp',
        string $lockType = 'script'
    ) {
        $this->logger = $logger;

        $cleanLockName = str_replace(' ', '_', StringUtil::cleanString($lockName));

        if (empty($cleanLockName)) {
            $this->logger->logError('Lock Manager - Log identifier not valid');
            exit;
        }

        $this->lockFolder = $lockFolder;
        $this->lockType = $lockType;
        $this->lockName = $cleanLockName;
        $this->secondsToRemoveLock = $secondsToRemoveLock;

        $this->logPrefix = 'LockManager App: ' . $this->lockName . ' ::: ';
    }

    public function checkLock(): bool
    {
        if (!file_exists($this->getLockFolderPath()) || !file_exists($this->getLockFullPath())) {
            return false;
        }

        return true;
    }

    public function setLock(): bool
    {
        if (true === $this->checkLock()) {
            return false;
        }

        if (!file_exists($this->getLockFolderPath())) {
            if (false === @mkdir($this->getLockFolderPath())) {
                $this->logger->logError($this->logPrefix . 'Failed to create lock folder');
                exit;
            }
        }

        file_put_contents($this->getLockFullPath(), time());

        return true;
    }

    public function updateLock(): bool
    {
        if (!file_exists($this->getLockFolderPath())) {
            if (false === @mkdir($this->getLockFolderPath())) {
                $this->logger->logError($this->logPrefix . 'Failed to create lock folder');
                exit;
            }
        }

        file_put_contents($this->getLockFullPath(), time());

        return true;
    }

    public function removeLock(): bool
    {
        if (file_exists($this->getLockFullPath())) {
            unlink($this->getLockFullPath());
        }

        return true;
    }

    public function shouldLockBeRemoved(): bool
    {
        if (!isset($this->secondsToRemoveLock)) {
            return false;
        }

        $res = $this->getLockAge() > $this->secondsToRemoveLock;

        if ($res) {
            $this->logger->logInfo(
                $this->logPrefix .
                'Removing lock, it has been locked for more than '
                . $this->secondsToRemoveLock
                . ' seconds'
            );
        }

        return $res;
    }

    public function getSecondsToRemoveLock(): int
    {
        return abs($this->getLockAge() - $this->secondsToRemoveLock);
    }

    public function isRunning(): bool
    {
        $isLocked = $this->checkLock();

        if ($isLocked && $this->shouldLockBeRemoved()) {
            $this->removeLock();

            return false;
        }

        return $isLocked;
    }

    private function getLockAge(): int
    {
        if (!file_exists($this->getLockFullPath())) {
            return 0;
        } else {
            return (int) time() - filectime($this->getLockFullPath());
        }
    }

    private function getLockFolderPath(): string
    {
        return $this->lockFolder . '/' . $this->lockType . 'locks';
    }

    private function getLockFullPath(): string
    {
        return $this->getLockFolderPath() . '/.lock_' . $this->lockName;
    }
}
