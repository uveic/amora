<?php

namespace Amora\Core\Util;

use Amora\Core\Core;

final readonly class CsvReaderUtil
{
    public static function read(
        string $fullPathToFile,
        bool $firstRowAsAsHeader = false,
        bool $cleanData = false,
        string $separator = ',',
    ): array {
        $logger = Core::getLogger();

        if (!file_exists($fullPathToFile)) {
            $logger->logError('CSV file does not exist: ' . $fullPathToFile);
            return [];
        }

        $handle = fopen($fullPathToFile, 'r');
        if ($handle === false) {
            $logger->logError('Failed to open file for reading: ' . $fullPathToFile);
            return [];
        }

        $headers = [];
        $count = 0;
        $output = [];

        do {
            $data = fgetcsv(
                stream: $handle,
                separator: $separator,
                escape: "\\",
            );

            $count++;

            if (!$data || self::isEmpty($data)) {
                continue;
            }

            if ($firstRowAsAsHeader && $count === 1) {
                foreach ($data as $index => $datum) {
                    $header = $cleanData
                        ? strtolower(trim(StringUtil::cleanString(StringUtil::convertToUtf8($datum))))
                        : $datum;

                    if (strlen($header) > 0) {
                        $headers[$index] = $header;
                    }
                }

                continue;
            }

            $row = [];
            foreach ($data as $index => $datum) {
                $text = $cleanData
                    ? trim(StringUtil::convertToUtf8($datum))
                    : $datum;

                if ($headers && isset($headers[$index])) {
                    $row[$headers[$index]] = $text;
                } else {
                    $row[] = $text;
                }
            }

            $output[] = $row;
        } while($data !== false);

        fclose($handle);

        $logger->logInfo('CSV file successfully read: ' . $fullPathToFile);
        $logger->logInfo('Total lines: ' . count($output));

        return $output;
    }

    private static function isEmpty(array $data): bool
    {
        $lenCount = [];
        foreach ($data as $datum) {
            $len = strlen(trim($datum));
            if ($len > 0) {
                return false;
            }

            $lenCount[] = $len;
        }

        $results = array_count_values($lenCount);
        return isset($results[0]) && count($results) === 1;
    }
}
