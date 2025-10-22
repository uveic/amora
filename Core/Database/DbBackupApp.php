<?php

namespace Amora\Core\Database;

use Amora\Core\App\App;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Logger;
use DateTime;
use DateTimeZone;

final class DbBackupApp extends App
{
    public function __construct(
        Logger $logger,
        private readonly MySqlDb $db,
        private readonly string $backupFolderPath,
        private readonly string $mysqlCommand,
        private readonly string $mysqlDumpCommand,
        private readonly string $gzipCommand,
    ) {
        parent::__construct(
            logger: $logger,
            appName: 'Db Backup App - DB: ' . $this->db->name,
            lockMaxTimeSinceLastSyncSeconds: 3600,
            isPersistent: false
        );
    }

    public function run(): void
    {
        $this->execute(function () {
            $res = $this->createDbBackup();

            if ($res) {
                $this->deleteOutdatedBackups();
            }
        });
    }

    private function generateDbBackupFileName(): string
    {
        $now = DateUtil::convertStringToDateTimeImmutable(
            date: 'now',
            timezone: new DateTimeZone('UTC'),
        );

        return 'backup_' . $this->db->name
            . $now->format("_Y-m-d_H\hi\m")
            . ".sql.gz";
    }

    public function createDbBackup(): bool
    {
        $this->log('Backing up existing database...');

        $backupFileName = $this->generateDbBackupFileName();
        $backupFileFullPath = $this->backupFolderPath . $backupFileName;

        $this->log('Backing up to ' . $backupFileFullPath);

        exec(
            "$this->mysqlDumpCommand -u {$this->db->user} \
            -p'{$this->db->password}' \
            --single-transaction \
            --skip-lock-tables \
            --add-drop-table \
            --add-locks \
            --create-options \
            --extended-insert \
            --quick \
            --set-charset \
            {$this->db->name} \
            | $this->gzipCommand > $backupFileFullPath"
        );

        if (!file_exists($backupFileFullPath)) {
            $this->log('Aborting... Backup failed: ' . $backupFileFullPath);
            return false;
        }

        if (filesize($backupFileFullPath) <= 512) {
            $this->log('Aborting... File size not valid: ' . $backupFileFullPath);
            return false;
        }

        $this->log('Database backup complete');

        return true;
    }

    public function restoreLatestBackup(): bool
    {
        $this->log('Restoring latest backup...');

        $backupFilename = $this->getLatestBackupFilename();
        $backupFullPath = $this->backupFolderPath . $backupFilename;

        $this->log('Restoring backup: ' . $backupFilename);

        exec("$this->gzipCommand -dkf " . $backupFullPath);

        $backupFilenameUnzipped = str_replace('.gz', '', $backupFilename);
        $unzippedFullPath = $this->backupFolderPath . $backupFilenameUnzipped;

        exec(
            "$this->mysqlCommand -u {$this->db->user} \
             -p'{$this->db->password}' \
             {$this->db->name} \
             < $unzippedFullPath"
        );

        unlink($unzippedFullPath);

        $this->log('Restoring latest backup done');

        return true;
    }

    public function deleteOutdatedBackups(): bool
    {
        $this->log('Checking for outdated backups...');

        $now = DateUtil::convertStringToDateTimeImmutable(date: 'now', timezone: new DateTimeZone('UTC'));
        $hour = (int)$now->format('G');
        if ($hour < 3 || $hour > 6) {
            // Delete outdated files only between 3 and 6 AM UTC
            return true;
        }

        $this->log('Deleting outdated backup files...');

        $utcTz = new DateTimeZone('UTC');
        $twoDaysAgo = DateUtil::convertStringToDateTimeImmutable(date: '-2 days', timezone: $utcTz);
        $twoMonthsAgo = DateUtil::convertStringToDateTimeImmutable(date: '-2 months', timezone: $utcTz);

        $files = $this->getBackupFiles();
        foreach ($files as $file) {
            $datePart = substr(str_replace('.sql.gz', '', $file), -17);
            $fileDate = DateTime::createFromFormat(
                format: 'Y-m-d_H\hi\m',
                datetime: $datePart,
                timezone: $utcTz
            );

            if ($fileDate < $twoMonthsAgo && (int)$fileDate->format('G') === 4) {
                continue;
            }

            if ($fileDate < $twoDaysAgo) {
                continue;
            }

            $this->log('Deleting file: ' . $file);
            unlink($this->backupFolderPath . $file);
        }

        $this->log('Deleting outdated backup files done');

        return true;
    }

    private function getLatestBackupFilename(): string
    {
        $files = $this->getBackupFiles();
        return $files ? $files[count($files) - 1] : '';
    }

    private function getBackupFiles(): array
    {
        if (empty($this->backupFolderPath) || ! is_dir($this->backupFolderPath)) {
            $this->log('Backup folder path not found: ' . $this->backupFolderPath, true);
            return [];
        }

        // Check file format: backup_DbName_YYYY-mm-dd_HHhMMm.sql.gz
        $regex = "/backup_{$this->db->name}_\d{4}-\d{2}-\d{2}_\d{2}h\d{2}m.sql.gz/";
        $files = scandir($this->backupFolderPath);

        $output = [];
        foreach ($files as $file) {
            if (preg_match($regex, $file) === 0) {
                continue;
            }

            $output[] = $file;
        }

        sort($output);

        return $output;
    }
}
