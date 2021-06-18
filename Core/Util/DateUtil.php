<?php

namespace Amora\Core\Util;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Amora\Core\Core;
use Exception;
use Throwable;

final class DateUtil
{

    /**
     * Check if the date is a valid ISO8601 date: YYYY-mm-ddTHH:mm:ssZ
     * https://xml2rfc.tools.ietf.org/public/rfc/html/rfc3339.html#anchor14
     * @param string|null $isoDate
     * @return bool
     */
    public static function isValidDateISO8601(?string $isoDate): bool
    {
        if (empty($isoDate)) {
            return false;
        }

        $dateObj = DateTimeImmutable::createFromFormat(DateTimeImmutable::ISO8601, $isoDate);
        if ($dateObj === false) {
            return false;
        }

        $errors = DateTimeImmutable::getLastErrors();
        if (!empty($errors['warning_count'])) {
            return false;
        }

        // For some reason DateTime::createFromFormat does not check if the year has four digits
        // It accepts as valid strings like: 25-01-01T00:00:00Z
        if ((int) $dateObj->format('Y') < 1000) {
            return false;
        }

        return true;
    }

    /**
     * Check if the date is a valid for MySQL: Y-m-d H:i:s
     * @param string|null $date
     * @return bool
     */
    public static function isValidDateForMySql(?string $date): bool
    {
        if (empty($date)) {
            return false;
        }

        $tmpDate = DateTime::createFromFormat("Y-m-d H:i:s", $date);
        if ($tmpDate === false) {
            return false;
        }

        $errors = DateTime::getLastErrors();
        if (!empty($errors['warning_count'])) {
            return false;
        }

        // For some reason DateTime::createFromFormat does not check if the year has four digits
        // It accepts as valid strings like: 25-01-01T00:00:00Z
        if ((int) $tmpDate->format('Y') < 1000) {
            return false;
        }

        return true;
    }

    public static function convertDateFromISOToMySQLFormat(string $isoDate): ?string
    {
        if (!self::isValidDateISO8601($isoDate)) {
            Core::getDefaultLogger()->logWarning(
                'DateUtil - Convert date from ISO to MySQL - ISO date not valid: ' . $isoDate
            );
            return null;
        }
        return date('Y-m-d H:i:s', strtotime($isoDate));
    }

    public static function convertDateFromMySQLFormatToISO(string $date): ?string
    {
        if (!self::isValidDateForMySql($date)) {
            Core::getDefaultLogger()->logWarning(
                'DateUtil - Convert date from MySQL to ISO - MySQL date not valid: ' . $date
            );
            return null;
        }

        return date('c', strtotime($date));
    }

    public static function getCurrentDateForMySql(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function getDateForMySqlFrom(string $dateString = 'now'): string
    {
        $d = new DateTimeImmutable($dateString);
        return $d->format('Y-m-d H:i:s');
    }

    public static function getMySqlDateFromUnixTime(int $unixTime): string
    {
        return date('Y-m-d H:i:s', $unixTime);
    }

    public static function getTimestamp(): string
    {
        $microSeconds = microtime(true);
        $seconds = floor($microSeconds);

        $microSecondsInt = (int) (($microSeconds - $seconds) * 1000);

        return date('YmdHis') . $microSecondsInt;
    }

    /**
     * Get time elapsed string
     *
     * Example:
     * 4 months ago
     * 4 months, 2 weeks, 3 days, 1 hour, 49 minutes, 15 seconds ago
     *
     * @param string $datetime
     * @param string $language
     * @param bool $full
     * @param bool $includePrefixAndOrSuffix
     * @param bool $includeSeconds
     * @param string $timezone
     * @return string
     * @throws Exception
     */
    public static function getElapsedTimeString(
        string $datetime,
        string $language = 'EN',
        bool $full = false,
        bool $includePrefixAndOrSuffix = false,
        bool $includeSeconds = false,
        string $timezone = 'UTC',
    ): string {
        try {
            $tz = new DateTimeZone($timezone);
        } catch (Throwable) {
            $tz = new DateTimeZone('UTC');
        }
        $now = new DateTimeImmutable('now', $tz);
        $ago = new DateTimeImmutable($datetime, $tz);
        $diff = (array)$now->diff($ago);

        switch (strtoupper($language)) {
            case 'GL':
                $prefix = 'hai ';
                $suffix = '';
                $and = ' e ';
                $string = [
                    'y' => 'ano',
                    'm' => 'mes',
                    'd' => 'día',
                    'h' => 'hora',
                    'i' => 'minuto',
                    's' => 'segundo',
                ];
                break;
            case 'ES':
                $prefix = 'hace ';
                $suffix = '';
                $and = ' y ';
                $string = [
                    'y' => 'año',
                    'm' => 'mes',
                    'd' => 'día',
                    'h' => 'hora',
                    'i' => 'minuto',
                    's' => 'segundo',
                ];
                break;
            default:
                $prefix = '';
                $suffix = ' ago';
                $and = ' and ';
                $string = [
                    'y' => 'year',
                    'm' => 'month',
                    'd' => 'day',
                    'h' => 'hour',
                    'i' => 'minute',
                    's' => 'second',
                ];
                break;
        }

        if (!$includeSeconds &&
            ($diff['y'] > 0
                || $diff['m'] > 0
                || $diff['d'] > 0
                || $diff['h'] > 0
                || $diff['i'] > 0)
        ) {
            unset($string['s']);
        }

        if (!$includePrefixAndOrSuffix) {
            $prefix = '';
            $suffix = '';
        }

        foreach ($string as $key => &$value) {
            if (!empty($diff[$key])) {
                $value = $diff[$key] . ' ' . $value . ($diff[$key] > 1 ? 's' : '');
            } else {
                if (count($string) > 1) {
                    unset($string[$key]);
                }
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }

        $output = '';
        $count = 1;
        foreach ($string as $item) {
            $output .= $item;
            if ($count === count($string)) {
                break;
            }

            if ($count++ !== count($string) - 1) {
                $output .= ', ';
            } else {
                $output .= $and;
            }
        }

        return $prefix . $output . $suffix;
    }

    public static function formatUtcDateShort(
        ?string $stringDate = null,
        string $timezone = 'UTC',
        bool $includeTime = true,
        bool $includeSeconds = false,
    ): string {
        if (!isset($stringDate)) {
            $stringDate = 'now';
        }

        $utcDate = new DateTime($stringDate, new DateTimeZone('UTC'));
        $outputTzDate = $utcDate->setTimezone(new DateTimeZone($timezone));

        $format = 'd/m/Y';
        $format .= $includeTime
            ? ' H:i' . ($includeSeconds ? ':s' : '')
            : '';

        return $outputTzDate->format($format);
    }

    public static function formatUtcDate(
        ?string $stringDate = null,
        string $lang = 'EN',
        bool $includeWeekDay = true,
        bool $includeTime = false,
        string $timezone = 'UTC',
        bool $includeYear = true,
        bool $includeDay = true,
        bool $includeSeconds = false,
        bool $includeMonthYearSeparator = false,
    ): string {
        if (!isset($stringDate)) {
            $stringDate = 'now';
        }

        $utcDate = new DateTime($stringDate, new DateTimeZone('UTC'));
        $outputTzDate = $utcDate->setTimezone(new DateTimeZone($timezone));

        return self::formatDate(
            date: $outputTzDate,
            lang: $lang,
            includeWeekDay: $includeWeekDay,
            includeTime: $includeTime,
            timezone: $timezone,
            includeYear: $includeYear,
            includeDay: $includeDay,
            includeSeconds: $includeSeconds,
            includeMonthYearSeparator: $includeMonthYearSeparator,
        );
    }

    public static function formatDate(
        DateTimeImmutable|DateTime $date,
        string $lang = 'EN',
        bool $includeWeekDay = true,
        bool $includeTime = false,
        string $timezone = 'UTC',
        bool $includeYear = true,
        bool $includeDay = true,
        bool $includeSeconds = false,
        bool $includeMonthYearSeparator = false,
    ): string {
        $outputTzDate = $date->setTimezone(new DateTimeZone($timezone));

        $timeFormat = 'H:i' . ($includeSeconds ? ':s' : '');

        $lang = strtoupper($lang);
        switch (strtoupper($lang)) {
            case 'GL':
                $months = ['xaneiro', 'febreiro', 'marzo', 'abril', 'maio', 'xuño', 'xullo',
                    'agosto', 'setembro', 'outubro', 'novembro', 'decembro'];
                $days = ['luns', 'martes', 'mércores', 'xoves', 'venres', 'sábado', 'domingo'];

                $weekDay = $includeWeekDay ? $days[$outputTzDate->format('N') - 1] . ', ' : '';
                $day = $includeDay ? $outputTzDate->format('j') . ' de ' : '';
                $time = $includeTime ? ' ás ' . $outputTzDate->format($timeFormat) : '';
                $year = $includeYear
                    ? ($includeMonthYearSeparator ? ' de ' : ' ') . $outputTzDate->format('Y')
                    : '';

                return $weekDay
                    . $day
                    . $months[$outputTzDate->format('n') - 1]
                    . $year
                    . $time;
            case 'ES':
                $months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto',
                    'septiembre', 'octubre', 'noviembre', 'dieciembre'];
                $days = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];

                $weekDay = $includeWeekDay ? $days[$outputTzDate->format('N') - 1] . ', ' : '';
                $day = $includeDay ? $outputTzDate->format('j') . ' de ' : '';
                $time = $includeTime ? ' a las ' . $outputTzDate->format($timeFormat) : '';
                $year = $includeYear
                    ? ($includeMonthYearSeparator ? ' de ' : ' ') . $outputTzDate->format('Y')
                    : '';

                return $weekDay
                    . $day
                    . $months[$outputTzDate->format('n') - 1]
                    . $year
                    . $time;
            default:
                $format = ($includeWeekDay ? 'l, ' : '')
                    . ($includeDay ? 'jS ' : '')
                    . 'F'
                    . ($includeYear ? ' Y' : '')
                    . ($includeTime ? ' \a\t ' . $timeFormat : '');

                return $outputTzDate->format($format);
        }
    }

    public static function isSummerTime(): bool
    {
        return date('I', time()) != 0;
    }

    public static function getTimezoneFromUtcOffset(int $offsetMinutes): string
    {
        return timezone_name_from_abbr('', $offsetMinutes * 60, 0);
    }

    public static function transformFromUtcTo(
        string $stringDate,
        string $timezone = 'UTC',
        string $outputFormat = 'Y-m-d H:i:s'
    ): string
    {
        $utcDate = new DateTime($stringDate, new DateTimeZone('UTC'));
        $outputTzDate = $utcDate->setTimezone(new DateTimeZone($timezone));
        return $outputTzDate->format($outputFormat);
    }

    public static function getMySqlAggregateFormat(string $range): string
    {
        return match ($range) {
            'minute' => "'%Y-%m-%dT%H:%i'",
            'hour' => "'%Y-%m-%dT%H'",
            'day' => "'%Y-%m-%d'",
            'month' => "'%Y-%m'",
            'year' => "'%Y'",
            default => "'%Y-%m'",
        };
    }

    public static function getPhpAggregateFormat(string $range): string
    {
        return match ($range) {
            'minute' => 'Y-m-d H:i',
            'hour' => 'Y-m-d H',
            'day' => 'Y-m-d',
            'month' => 'Y-m',
            'year' => 'Y',
            default => 'Y-m',
        };
    }

    public static function convertPartialDateFormatToFullDate(
        string $partialDate,
        string $aggregatedBy,
        bool $roundUp = true
    ): string {
        $res = match ($aggregatedBy) {
            'minute' => $partialDate . ($roundUp ? ':59' : ':00'),
            'hour' => $partialDate . ($roundUp ? ':59:59' : ':00:00'),
            'day' => $partialDate . ($roundUp ? ' 23:59:59' : ' 00:00:00'),
            default => null,
        };

        if ($res) {
            return $res;
        }

        if ($aggregatedBy === 'month') {
            $now = new DateTimeImmutable('now');
            $month = substr($partialDate, -2);
            $n = new DateTimeImmutable($now->format('Y-' . $month . '-d H:i:s'));

            return $partialDate .
                ($roundUp
                    ? $n->format('n') . ' 23:59:59'
                    : '-01 00:00:00'
                );
        }

        if ($aggregatedBy === 'year') {
            return $partialDate . $roundUp ? '-12-31 23:59:59' : '-01-01 00:00:00';
        }

        $now = new DateTimeImmutable('now');
        return $now->format('Y-m-d H:i:s');
    }
}
