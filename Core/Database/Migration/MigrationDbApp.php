<?php

namespace Amora\Core\Database\migration;

use Exception;
use Amora\Core\Database\MySqlDb;

final class MigrationDbApp
{
    const MIGRATION_TABLE_NAME = 'migration';

    private MySqlDb $db;
    private string $pathToMigrationFiles;

    private array $validArguments = array(
        'install' => true,
        'migrate' => true,
        'new' => true
    );

    public function __construct(MySqlDb $db, string $pathToMigrationFiles)
    {
        $this->db = $db;
        $this->pathToMigrationFiles = $pathToMigrationFiles;
    }

    public function run($args)
    {
        $action = (empty($args[1])) ? 'empty' : trim($args[1]);

        if (!array_key_exists($action, $this->validArguments)) {
            $action = null;
        }

        switch ($action) {
            case 'install':
                if (!$this->isDbEmpty()) {
                    $question = 'The database is not empty. ' .
                        'Remove all tables/data and install a fresh database? (yes/no): ';
                    if (!$this->areYouSure($question)) {
                        $this->printOutput("Installation canceled");
                        break;
                    }
                }

                $this->createDbFromScratch();

                break;

            case 'migrate':
                $tables = $this->db->getTables();
                if (!in_array(self::MIGRATION_TABLE_NAME, $tables)) {
                    $this->createDbFromScratch();
                }

                $this->executeFiles();
                break;

            case 'new':
                if (!empty($args[2])) {
                    $this->createMigrationTemplate($args[2]);
                } else {
                    $this->createMigrationTemplate();
                }
                break;

            default:
                $this->printHelp();
                break;
        }
    }

    private function printHelp()
    {
        $this->printOutput();
        $this->printOutput('Available commands: ');
        $this->printOutput('   install     Installs a fresh database. It removes existing tables/data if any.');
        $this->printOutput('   migrate     Executes migration files.');
        $this->printOutput('   new         [filename] Creates migration file from template.');
        $this->printOutput();
    }

    private function askUser($question, $validAnswers = []): string
    {
        echo $this->getLineOutputPrefix() . $question;
        do {
            $ans = strtolower(trim(fgets(STDIN)));
            if (! in_array($ans, $validAnswers, true)) {
                echo $this->getLineOutputPrefix()
                    . "Response not valid. Please choose from [" . implode(", ", $validAnswers) . "]: ";
                $ans = false;
            }
        } while ($ans === false);

        return $ans;
    }

    private function areYouSure($question = ''): bool
    {
        if (empty($question)) {
            $question = "Are you sure? (yes/no): ";
        }
        $res = $this->askUser($question, ['yes', 'y', 'no', 'n']);

        return ($res == 'yes' || $res == 'y');
    }

    private function printOutput($str = "\n", $newLine = true): void
    {
        if ($newLine && strpos($str, PHP_EOL) === false) {
            $str .= PHP_EOL;
        }

        echo $this->getLineOutputPrefix() . $str;
    }

    private function createMigrationTemplate($filename = ''): void
    {
        if (empty($filename)) {
            $question = 'WARNING! A filename is highly recommended.' .
             'Continue with a generic name? (yes/no): ';
            if (! $this->areYouSure($question)) {
                $this->printOutput("Migration file creation aborted");
                exit;
            }
            $filename = 'migration_template';
        }

        $filename = mb_strtolower(trim($filename), 'UTF-8');
        $filename = str_replace('.php', '', $filename);
        // Remove non-alphanumeric characters
        $filename = preg_replace('~[^\p{L}\p{N}]++~u', '_', $filename);
        $filename = date("Y_m_d_His_") . $filename;

        $content = "";
        $content .= "<?php" . PHP_EOL;
        $content .= "/**" . PHP_EOL;
        $content .= " * Return SQL statement as a string" . PHP_EOL;
        $content .= " */" . PHP_EOL . PHP_EOL;
        $content .= "return \"\";" . PHP_EOL;

        if (! is_dir($this->pathToMigrationFiles)) {
            mkdir($this->pathToMigrationFiles, "0755", true);
        }
        $fp = fopen($this->pathToMigrationFiles . '/' . $filename . '.php', 'w');
        fwrite($fp, $content);
        fclose($fp);

        $this->printOutput("Template created: " . $this->pathToMigrationFiles . '/' . $filename . '.php');
    }

    private function createDbFromScratch(): void
    {
        $this->clearExistingDB();

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

        $this->printOutput('Done. Fresh database installed.');
    }

    private function clearExistingDB(): bool
    {
        $this->printOutput('Deleting existing tables (if any)...');

        $tables = $this->db->getTables();

        if ($tables) {
            $i = 0;
            $this->db->execute('SET foreign_key_checks = 0');

            foreach ($tables as $value) {
                $this->printOutput('Removing ' . $value . '...');
                $this->db->execute('DROP TABLE IF EXISTS '. $value . ';');
                $i++;
            }
        }

        $this->printOutput('Existing tables removed');

        return true;
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

        $executed = $this->db->select(self::MIGRATION_TABLE_NAME);

        // Check if files are in this format YYYY_MM_DD_HH_MM_SS_filename_description.php
        $regex = "/\d{4}_\d{2}_\d{2}_\d{6}_[0-9a-zA-Z_-]+.php/";
        $files = scandir($this->pathToMigrationFiles);

        foreach ($files as $key => $file) {
            if (preg_match($regex, $file) === 0) {
                unset($files[$key]);
                continue;
            }
            if ($this->inArrayR($file, $executed)) {
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

    /**
     * @param string $filename
     * @return bool
     * @throws Exception
     */
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
                    return ['success' => false];
                }

                $this->db->insert(self::MIGRATION_TABLE_NAME, ['filename' => $filename]);

                return ['success' => true];
            }
        );

        return empty($res['success']) ? false : true;
    }

    private function isDbEmpty()
    {
        $tables = $this->db->getTables();
        return empty($tables);
    }

    //////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////
    ////////// HELPER FUNCTIONS

    /**
     * Same that in_array() but for multidimensional arrays
     * @param $needle
     * @param $haystack
     * @param bool $strict
     * @return bool
     */
    private function inArrayR($needle, $haystack, $strict = false)
    {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle)
                || (
                    is_array($item)
                    && $this->inArrayR($needle, $item, $strict)
                )
            ) {
                return true;
            }
        }

        return false;
    }

    private function getLineOutputPrefix(): string
    {
        return 'DB: ' . $this->db->getDbName() . ' ::: ';
    }
}
