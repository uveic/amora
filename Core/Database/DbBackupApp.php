<?php

namespace Amora\Core\Database;

use Amora\Core\App\App;
use Amora\Core\Logger;
use DateTime;
use DateTimeZone;

final class DbBackupApp extends App
{
    public function __construct(
        Logger $logger,
        private MySqlDb $db,
        private string $backupFolderPath,
        private string $mysqlCommand,
        private string $mysqlDumpCommand,
        private string $gzipCommand,
    ) {
        parent::__construct(
            logger: $logger,
            appName: 'Db Backup App - DB: ' . $this->db->getDbName(),
            lockMaxTimeSinceLastSyncSeconds: 3600,
            isPersistent: false
        );

        $this->backupFolderPath = rtrim($this->backupFolderPath, ' /') . '/';
    }

    public function run()
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
        $now = new DateTime(timezone: new DateTimeZone('UTC'));

        return 'backup_' . $this->db->getDbName()
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
            "{$this->mysqlDumpCommand} -u {$this->db->getUser()} \
            -p'{$this->db->getPassword()}' \
            --single-transaction \
            --flush-logs \
            --add-drop-table \
            --add-locks \
            --create-options \
            --disable-keys \
            --extended-insert \
            --quick \
            --set-charset \
            {$this->db->getDbName()} \
            | {$this->gzipCommand} > {$backupFileFullPath}"
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

        exec("{$this->gzipCommand} -dkf " . $backupFullPath);

        $backupFilenameUnzipped = str_replace('.gz', '', $backupFilename);
        $unzippedFullPath = $this->backupFolderPath . $backupFilenameUnzipped;

        exec(
            "{$this->mysqlCommand} -u {$this->db->getUser()} \
             -p'{$this->db->getPassword()}' \
             {$this->db->getDbName()} \
             < {$unzippedFullPath}"
        );

        unlink($unzippedFullPath);

        $this->log('Restoring latest backup done');

        return true;
    }

    public function deleteOutdatedBackups(): bool
    {
        $this->log('Checking for outdated backups...');

        $now = new DateTime(timezone: new DateTimeZone('UTC'));
        $hour = (int)$now->format('G');
        if ($hour < 3 || $hour > 6) {
            // Delete outdated files only between 3 and 6 AM UTC
            return true;
        }

        $this->log('Deleting outdated backup files...');

        $utcTz = new DateTimeZone('UTC');
        $oneDayAgo = new DateTime(timezone: $utcTz);
        $oneDayAgo->setTimestamp(strtotime("-1 days"));

        $files = $this->getBackupFiles();
        foreach ($files as $file) {
            $datePart = substr(str_replace('.sql.gz', '', $file), -17);
            $fileDate = DateTime::createFromFormat(
                format: 'Y-m-d_H\hi\m',
                datetime: $datePart,
                timezone: $utcTz
            );

            if ($fileDate < $oneDayAgo) {
                $this->log('Deleting file: ' . $file);
                unlink($this->backupFolderPath . $file);
            }
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

        // Check if files are in this format 'backup_DdName_YYYY-mm-dd_HHhMMm.sql.gz'
        $regex = "/backup_{$this->db->getDbName()}_\d{4}-\d{2}-\d{2}_\d{2}h\d{2}m.sql.gz/";
        $files = scandir($this->backupFolderPath);

        foreach ($files as $key => $file) {
            if (preg_match($regex, $file) === 0) {
                unset($files[$key]);
                continue;
            }
        }

        sort($files);

        return $files;
    }
}