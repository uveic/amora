<?php

namespace Amora\Core\Util;

final class CsvWriterUtil
{
    public static function write(
        string $fileName,
        array $data,
        bool $includeArrayKeysAsHeader = true,
    ): bool {
        if (empty($fileName)) {
            echo 'No valid file: ' . $fileName . PHP_EOL;
            return false;
        }

        if (empty($data)) {
            echo 'No data' . PHP_EOL;
            return false;
        }

        $handle = fopen($fileName, 'w');

        if ($handle === false) {
            echo 'Failed to open file for writing: ' . $fileName . PHP_EOL;
            return false;
        }

        if (is_null($handle)) {
            echo 'Cannot write CSV data to already closed file: ' . $fileName . PHP_EOL;
            return false;
        }

        if ($includeArrayKeysAsHeader) {
            if (false === self::writeLine($handle, array_keys($data[0]))) {
                echo 'Failed to write CSV data to file: ' . $fileName . PHP_EOL;
                return false;
            }
        }

        foreach ($data as $datum) {
            if (false === self::writeLine($handle, array_values($datum))) {
                echo 'Failed to write CSV data to file: ' . $fileName . PHP_EOL;
                return false;
            }
        }

        fclose($handle);

        return true;
    }

    private static function writeLine($handle, array $data): bool {
        if (false === fputcsv($handle, array_values($data))) {
            return false;
        }

        return true;
    }
}
