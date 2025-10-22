<?php

namespace Amora\Core\Database\migration;

use Amora\Core\Core;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Database\MySqlDb;

final class MigrationDbApp
{
    const string MIGRATION_TABLE_NAME = 'migration';

    private array $validArguments = array(
        'install' => true,
        'migrate' => true,
        'new' => true
    );

    public function __construct(
        private readonly MySqlDb $db,
        private readonly string $pathToMigrationFiles,
    ) {}

    public function run($args): void
    {
        $action = (empty($args[1])) ? 'empty' : trim($args[1]);

        if (!array_key_exists($action, $this->validArguments)) {
            $action = null;
        }

        switch ($action) {
            case 'install':
                if (Core::isRunningInLiveEnv() && !$this->isDbEmpty()) {
                    $question = PHP_EOL .
                        '##################################################' . PHP_EOL .
                        '#######                                    #######' . PHP_EOL .
                        '#######           WARING!                  #######' . PHP_EOL .
                        '#######                                    #######' . PHP_EOL .
                        '#######    You are about to erase all      #######' . PHP_EOL .
                        '#######    data and tables. This is        #######' . PHP_EOL .
                        '#######    unrecoverable. Are you sure?    #######' . PHP_EOL .
                        '#######                                    #######' . PHP_EOL .
                        '##################################################' . PHP_EOL . PHP_EOL .
                        'Type ERASE to remove all tables/data and install a fresh database: ';
                    if (!$this->areYouSure($question)) {
                        $this->printOutput("Installation canceled");
                        break;
                    }
                }

                $this->createDbFromScratch();

                break;

            case 'migrate':
                $tables = $this->db->getTables();
                if (!in_array(self::MIGRATION_TABLE_NAME, $tables, true)) {
                    $this->createMigrationTable();
                }

                $this->executeFiles();
                break;

            case 'new':
                $this->createMigrationTemplate($args[2] ?? null);
                break;

            default:
                $this->printHelp();
                break;
        }
    }

    private function printHelp(): void
    {
        $this->printOutput();
        $this->printOutput('Available commands: ');
        $this->printOutput('   install     Installs a fresh database. It removes existing tables/data if any.');
        $this->printOutput('   migrate     Executes migration files.');
        $this->printOutput('   new         [filename] Creates migration file from template.');
        $this->printOutput();
    }

    private function areYouSure(string $question = ''): bool
    {
        if (empty($question)) {
            $question = "Are you sure? (yes/no): ";
        }
        echo $this->getLineOutputPrefix() . $question;
        $ans = strtolower(trim(fgets(STDIN)));

        return ($ans === 'erase');
    }

    private function printOutput($str = PHP_EOL): void
    {
        if (!str_contains($str, PHP_EOL)) {
            $str .= PHP_EOL;
        }

        echo $this->getLineOutputPrefix() . $str;
    }

    private function createMigrationTemplate(?string $filename): void
    {
        if (empty($filename)) {
            $filename = 'migration_template';
        }

        $filename = mb_strtolower(trim($filename), 'UTF-8');
        $filename = str_replace('.php', '', $filename);
        // Remove non-alphanumeric characters
        $filename = preg_replace('~[^\p{L}\p{N}]++~u', '_', $filename);
        $filename = date("Y_m_d_His_") . $filename;

        $content = "<?php" . PHP_EOL;
        $content .= "/**" . PHP_EOL;
        $content .= " * Return SQL statement as a string" . PHP_EOL;
        $content .= " */" . PHP_EOL . PHP_EOL;
        $content .= "return \"\";" . PHP_EOL;

        if (!is_dir($this->pathToMigrationFiles) &&
            !mkdir($concurrentDirectory = $this->pathToMigrationFiles, "0755", true) &&
            !is_dir($concurrentDirectory)
        ) {
            $this->printOutput(sprintf('Error creating directory: %s', $concurrentDirectory));
            return;
        }
        $fp = fopen($this->pathToMigrationFiles . '/' . $filename . '.php', 'wb');
        fwrite($fp, $content);
        fclose($fp);

        $this->printOutput("Template created: " . $this->pathToMigrationFiles . '/' . $filename . '.php');
    }

    private function createDbFromScratch(): void
    {
        $this->clearExistingDB();
        $this->createMigrationTable();

        $this->printOutput('Done. Fresh database installed.');
    }

    private function createMigrationTable(): void
    {
        $sql = "
            DROP TABLE IF EXISTS " . self::MIGRATION_TABLE_NAME . ";
            CREATE TABLE " . self::MIGRATION_TABLE_NAME . " (
                id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                filename VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        $this->db->execute($sql);

        $this->printOutput('Migration table created.');
    }

    private function clearExistingDB(): void
    {
        $this->printOutput('Deleting existing tables (if any)...');

        $tables = $this->db->getTables();

        if ($tables) {
            $this->db->execute('SET foreign_key_checks = 0');

            foreach ($tables as $value) {
                $this->printOutput('Removing ' . $value . '...');
                $this->db->execute('DROP TABLE IF EXISTS '. $value . ';');
            }
        }

        $this->printOutput('Existing tables removed');
    }

    /**
     * Returns an array with all files in the directory path which name matches $namePattern
     * Pattern: YYYY_MM_DD_HHMMSS_any_migration_name.php
     */
    private function getMigrationFiles(): array
    {
        if (empty($this->pathToMigrationFiles) || ! is_dir($this->pathToMigrationFiles)) {
            $this->printOutput(
                'Migrate files folder path not found: ' . $this->pathToMigrationFiles
            );

            return [];
        }

        $executed = $this->fetchFilesFromDb();

        // Check if files are in this format YYYY_MM_DD_HH_MM_SS_filename_description.php
        $regex = "/\d{4}_\d{2}_\d{2}_\d{6}_[0-9a-zA-Z_-]+.php/";
        $files = scandir($this->pathToMigrationFiles);

        foreach ($files as $key => $file) {
            if (preg_match($regex, $file) === 0) {
                unset($files[$key]);
                continue;
            }

            if (isset($executed[$file])) {
                unset($files[$key]);
            }
        }

        sort($files);

        return $files;
    }

    private function executeFiles(): void
    {
        $files = $this->getMigrationFiles();
        if (empty($files)) {
            $this->printOutput("Nothing to do, database is up to date");
            return;
        }

        $this->printOutput("Executing migration files...");

        foreach ($files as $file) {
            $this->printOutput("Executing file: " . $file);
            $res = $this->executeFile($file);
            if (empty($res)) {
                $this->printOutput('Error executing file: ' . $file . ' - Aborting...');
                exit;
            }
            $this->printOutput("File successfully executed: " . $file);
        }

        $this->printOutput("Database migrated successfully");
    }

    private function executeFile(string $filename): bool
    {
        $path = $this->pathToMigrationFiles . '/' . $filename;

        if (!file_exists($path)) {
            return false;
        }

        $sql = require $path;

        $res = $this->db->withTransaction(
            function () use ($sql, $filename) {
                $resExecute = $this->db->execute($sql);

                if (empty($resExecute)) {
                    return new Feedback(false);
                }

                $this->db->insert(self::MIGRATION_TABLE_NAME, ['filename' => $filename]);

                return new Feedback(true);
            }
        );

        return $res->isSuccess;
    }

    private function isDbEmpty(): bool
    {
        $tables = $this->db->getTables();
        return empty($tables);
    }

    //////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////
    ////////// HELPER FUNCTIONS

    private function fetchFilesFromDb(): array
    {
        $files = $this->db->fetchAll(
            '
                SELECT `filename`
                FROM ' . self::MIGRATION_TABLE_NAME . '
                ORDER BY id
            '
        );

        $output = [];
        foreach ($files as $file) {
            $output[$file['filename']] = true;
        }

        return $output;
    }

    private function getLineOutputPrefix(): string
    {
        return 'DB: ' . $this->db->name . ' ::: ';
    }
}
