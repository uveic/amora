<?php

namespace Amora\Core\Util;

use Amora\Core\Core;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

enum LogPriority
{
    case DEBUG;
    case INFO;
    case WARNING;
    case ERROR;
}

final class Logger
{
    private readonly string $appName;
    private readonly string $prefix;
    private readonly bool $isEnabled;
    private int $startTime;
    private int $lastCheckpointTime = 0;

    public function __construct(
        private readonly ?string $identifier = null,
        private readonly bool $isRunningInCli = false,
    ) {
        $this->appName = Core::getConfig()->appName;
        $this->isEnabled = Core::getConfig()->isLoggingEnabled;
        $this->prefix = $this->getPrefix();
        $this->startTime = $this->getMicroTime();
    }

    ///////////////////////////////////////////////////////////////////////////
    // Timer helpers

    public function getMicroTime(): int
    {
        return (int)round(microtime(true), 8) * 1000;
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

    public function logDebug($message, $pre = false): void
    {
        if (!$this->isEnabled) {
            return;
        }

        if (Core::isRunningInLiveEnv()) {
            return;
        }

        if ($pre) {
            echo '<pre>' . print_r($message, true) . '</pre>';
            return;
        }

        $this->logInfo($message);
    }

    public function logInfo(string $msg): void
    {
        if (!$this->isEnabled) {
            return;
        }

        $this->logMessage("[INFO] $msg (" . $this->getCheckpointTime() . ' ms)', LogPriority::INFO);
    }

    public function logWarning(string $msg, array $metaData = []): void
    {
        if (!$this->isEnabled) {
            return;
        }

        $message = "[WARNING] $msg (" .
            $this->getCheckpointTime() . ' ms), Metadata: ' .
            json_encode($metaData);
        $this->logMessage($message, LogPriority::WARNING);
    }

    public function logError(string $msg): void
    {
        if (!$this->isEnabled) {
            return;
        }

        $message = "[ERROR] $msg (" . $this->getCheckpointTime() . ' ms)';
        $this->logMessage($message, LogPriority::ERROR);
    }

    public function logException(Throwable $e): void
    {
        if (!$this->isEnabled) {
            return;
        }

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
     * @param LogPriority $priority
     */
    private function logMessage(string $msg, LogPriority $priority): void
    {
        try {
            $msg = $this->prefix . $msg;

            if (!Core::isRunningInLiveEnv()) {
                $f = fopen('php://stdout', 'w');
                fwrite($f, $msg . PHP_EOL);
                fclose($f);

                return;
            }

            if ($priority === LogPriority::WARNING || $priority === LogPriority::ERROR) {
                error_log($msg);
            }

            if ($this->isRunningInCli) {
                echo $msg, PHP_EOL;
            }

            $outPath = '/tmp/' . $this->appName . '_' . date('Ymd') . '.log';
            $f = fopen($outPath, 'a');

            if (empty($f)) {
                error_log("Failed to open log file: $outPath");
                return;
            }

            fwrite($f, $msg . PHP_EOL);
            fclose($f);
        } catch (Throwable $t) {
            error_log($t->getMessage() . PHP_EOL . $t->getTraceAsString());
        }
    }

    private function getPrefix(): string
    {
        $now = new DateTimeImmutable(timezone: new DateTimeZone('UTC'));
        $identifier = $this->identifier ? '[' . $this->identifier . ']' : '';
        return '[' . $now->format('D M d H:i:s Y') . ']' . $identifier . ' == ';
    }
}
