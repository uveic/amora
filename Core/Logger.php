<?php

namespace Amora\Core;

use Throwable;

final class Logger
{
    private int $startTime;
    private int $lastCheckpointTime = 0;

    public function __construct(
        private string $appName,
        private bool $isRunningInCli = false
    ) {
        $this->startTime = $this->getMicroTime();
    }

    ///////////////////////////////////////////////////////////////////////////
    // Timer helpers

    public function getMicroTime(): float
    {
        return round(microtime(true), 8) * 1000;
    }

    public function getCheckpointTime(): float
    {
        $elapsed = $this->lastCheckpointTime == 0
            ? 0
            : $this->getMicroTime() - $this->lastCheckpointTime;

        $this->lastCheckpointTime = $this->getMicroTime();

        return round($elapsed, 3);
    }

    public function getTotalTime(): float
    {
        return round(($this->getMicroTime() - $this->startTime) / 1000, 3);
    }

    ///////////////////////////////////////////////////////////////////////////
    // Logging helpers

    public function logInfo(string $msg): void
    {
        $this->logMessage("== [INFO] $msg (" . $this->getCheckpointTime() . ' ms)', LOG_INFO);
    }

    public function logWarning(string $msg, array $metaData = []): void
    {
        $message = "== [WARNING] $msg (" .
            $this->getCheckpointTime() . ' ms), Metadata: ' .
            json_encode($metaData);
        error_log($message);
        $this->logMessage($message, LOG_WARNING);
    }

    public function logError(string $msg): void
    {
        $message = "== [ERROR] $msg (" . $this->getCheckpointTime() . ' ms)';
        error_log($message);
        $this->logMessage($message, LOG_ERR);
    }

    public function logException(Throwable $e): void
    {
        $msg = preg_replace("#(\s{2,})#", " ", $e->getMessage());

        // Build stack trace
        $trace = $e->getTrace();

        $traceLines = array();

        foreach ($trace as $i => $l) {
            $traceLine = "[$i] ";

            if (isset($l['class'])) {
                $traceLine .= $l['class'];
            }

            if (isset($l['type'])) {
                $traceLine .= $l['type'];
            }

            if (isset($l['function'])) {
                $traceLine .= $l['function'];
            }

            if (isset($l['file'])) {
                $traceLine .= ' in ' . $l['file'];
            }

            if (isset($l['line'])) {
                $traceLine .= ' on line ' . $l['line'];
            }

            $traceLines[] = $traceLine;
        }

        $this->logError($msg . "\nTRACE:\n" . implode("\n", $traceLines));
    }

    /**
     * @param string $msg
     * @param int $priority
     */
    private function logMessage(string $msg, int $priority): void
    {
        if ($this->isRunningInCli) {
            echo $msg, PHP_EOL;
            return;
        }

        if (!Core::isRunningInLiveEnv()) {
            $f = fopen('php://stdout', 'w');
            fwrite($f, $msg . PHP_EOL);
            fclose($f);
        } else {
            openlog($this->appName, LOG_PID, LOG_LOCAL1);
            syslog($priority, $msg);
            closelog();
        }
    }

    public function logDebug(string $message, $pre = false): void
    {
        if (Core::isRunningInLiveEnv()) {
            return;
        }

        if ($pre) {
            echo '<pre>' . print_r($message, true) . '</pre>';
            return;
        }

        $this->logInfo($message);
    }
}
