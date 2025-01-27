<?php

namespace Amora\Core\Util;

use Amora\Core\Core;

final readonly class CsvReaderUtil
{
    public static function read(
        string $fullPathToFile,
        bool $firstRowAsAsHeader = false,
        bool $cleanData = false,
        array $expectedHeaders = [],
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

        $foundHeaders = [];
        $headers = [];
        $output = [];

        do {
            $data = fgetcsv(
                stream: $handle,
                separator: $separator,
                escape: "\\",
            );

            if (!$data || self::isEmpty($data)) {
                continue;
            }

            if ($firstRowAsAsHeader && !$headers) {
                foreach ($data as $index => $datum) {
                    $datum = isset($datum) ? trim($datum) : null;
                    $header = $cleanData
                        ? strtolower(StringUtil::cleanString(StringUtil::convertToUtf8($datum)))
                        : $datum;

                    if (!$header) {
                        $header = '#' . $index;
                    }

                    $foundHeaders[$header] = $index;
                    $headers[$index] = $header;
                }

                if ($expectedHeaders) {
                    foreach ($expectedHeaders as $expectedHeader) {
                        if (!isset($foundHeaders[$expectedHeader])) {
                            $headers = [];
                            $foundHeaders = [];
                            continue 2;
                        }
                    }

                    continue;
                }
            }

            $row = [];
            foreach ($data as $index => $datum) {
                $datum = isset($datum) ? trim($datum) : null;
                $text = $cleanData ? StringUtil::convertToUtf8($datum) : $datum;

                if ($headers && isset($headers[$index])) {
                    $row[$headers[$index]] = $text;
                } else {
                    $row[] = $text;
                }
            }

            if (count($headers) !== count($row)) {
                continue;
            }

            $output[] = $row;
        } while($data !== false);

        fclose($handle);

        $logger->logInfo('CSV file successfully read: ' . $fullPathToFile);
        $logger->logInfo('Total lines: ' . count($output));

        return $output;
    }

    private static function isEmpty(?array $data): bool
    {
        if (empty($data)) {
            return true;
        }

        foreach ($data as $datum) {
            $len = empty($datum) ? 0 : strlen(trim($datum));
            if ($len > 0) {
                return false;
            }
        }

        return true;
    }
}
