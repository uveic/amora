<?php

namespace Amora\Core\Util;

use Amora\Core\Core;

final class CsvWriterUtil
{
    public static function write(
        string $fileName,
        array $data,
        bool $includeArrayKeysAsHeader = true,
    ): bool {
        $logger = Core::getLogger('CsvWriterUtil');
        if (empty($fileName)) {
            $logger->logError('No valid file: ' . $fileName);
            return false;
        }

        if (empty($data)) {
            $logger->logError('No data');
            return false;
        }

        $handle = fopen($fileName, 'w');

        if ($handle === false) {
            $logger->logError('Failed to open file for writing: ' . $fileName);
            return false;
        }

        if (is_null($handle)) {
            $logger->logError('Cannot write CSV data to already closed file: ' . $fileName);
            return false;
        }

        if ($includeArrayKeysAsHeader) {
            if (false === self::writeLine($handle, array_keys($data[0]))) {
                $logger->logError('Failed to write CSV data to file: ' . $fileName);
                return false;
            }
        }

        foreach ($data as $datum) {
            if (false === self::writeLine($handle, array_values($datum))) {
                $logger->logError('Failed to write CSV data to file: ' . $fileName);
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
