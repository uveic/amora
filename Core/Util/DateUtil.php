<?php

namespace Amora\Core\Util;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use Amora\Core\Core;
use Throwable;

final class DateUtil
{
    const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

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
            // Check if it is from Javascript and it contains milliseconds. Remove milliseconds if found.
            $isoDate = preg_replace(
                pattern: "/\.[0-9]{3}Z/",
                replacement: 'Z',
                subject: $isoDate,
            );

            $dateObj = DateTimeImmutable::createFromFormat(DateTimeImmutable::ISO8601, $isoDate);
            if ($dateObj === false) {
                return false;
            }
        }

        $errors = DateTimeImmutable::getLastErrors();
        if (!empty($errors['warning_count'])) {
            return false;
        }

        // For some reason DateTime::createFromFormat does not check if the year has four digits
        // It accepts strings like: 25-01-01T00:00:00Z as valid
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

        $tmpDate = DateTime::createFromFormat(self::MYSQL_DATETIME_FORMAT, $date);
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
        return date(self::MYSQL_DATETIME_FORMAT, strtotime($isoDate));
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
        return date(self::MYSQL_DATETIME_FORMAT);
    }

    public static function getDateForMySqlFrom(string $dateString = 'now'): string
    {
        $d = new DateTimeImmutable($dateString);
        return $d->format(self::MYSQL_DATETIME_FORMAT);
    }

    public static function getMySqlDateFromUnixTime(int $unixTime): string
    {
        return date(self::MYSQL_DATETIME_FORMAT, $unixTime);
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
     * @param DateTimeImmutable|DateTime $from
     * @param DateTimeImmutable|DateTime|null $to
     * @param string $language
     * @param bool $full
     * @param bool $includePrefixAndOrSuffix
     * @param bool $includeSeconds
     * @return string
     * @throws \Exception
     */
    public static function getElapsedTimeString(
        DateTimeImmutable|DateTime $from,
        DateTimeImmutable|DateTime|null $to = null,
        string $language = 'EN',
        bool $full = false,
        bool $includePrefixAndOrSuffix = false,
        bool $includeSeconds = false,
    ): string {
        if (!isset($to)) {
            $to = new DateTimeImmutable('now', $from->getTimezone());
        }
        $diff = (array)$to->diff($from);

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

    public static function convertStringToDateTimeImmutable(string $date): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable(datetime: $date);
        } catch (Throwable) {
            Core::getDefaultLogger()->logError('Error converting string to DateTimeImmutable: ' . $date);
            return new DateTimeImmutable();
        }
    }

    public static function formatDateShort(
        DateTimeImmutable|DateTime $date,
        bool $includeTime = true,
        bool $includeSeconds = false,
    ): string {
        $format = 'd/m/Y';
        $format .= $includeTime
            ? ' H:i' . ($includeSeconds ? ':s' : '')
            : '';

        return $date->format($format);
    }

    public static function formatDate(
        DateTimeImmutable|DateTime $date,
        string $lang = 'EN',
        bool $includeDay = true,
        bool $includeYear = true,
        bool $includeWeekDay = true,
        bool $includeTime = false,
        bool $includeSeconds = false,
        bool $includeDayTimeSeparator = true,
        bool $includeMonthYearSeparator = true,
        bool $includeDayMonthSeparator = true,
        bool $shortMonthName = false,
    ): string {
        $timeFormat = 'H:i' . ($includeSeconds ? ':s' : '');

        $lang = strtoupper($lang);
        switch (strtoupper($lang)) {
            case 'GL':
                $days = ['luns', 'martes', 'mércores', 'xoves', 'venres', 'sábado', 'domingo'];

                $weekDay = $includeWeekDay ? $days[$date->format('N') - 1] . ', ' : '';
                $day = $includeDay ? $date->format('j')
                    . ($includeDayMonthSeparator ? ' de ' : ' ')
                    : '';
                $time = $includeTime
                    ? ($includeDayTimeSeparator ? ' ás ' : ' ') . $date->format($timeFormat)
                    : '';
                $year = $includeYear
                    ? ($includeMonthYearSeparator ? ' de ' : ' ') . $date->format('Y')
                    : '';

                return $weekDay
                    . $day
                    . self::getMonthName($date->format('n'), $lang, $shortMonthName)
                    . $year
                    . $time;
            case 'ES':
                $days = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];

                $weekDay = $includeWeekDay ? $days[$date->format('N') - 1] . ', ' : '';
                $day = $includeDay ? $date->format('j')
                    . ($includeDayMonthSeparator ? ' de ' : ' ')
                    : '';
                $time = $includeTime
                    ? ($includeDayTimeSeparator ? ' a las ' : ' ') . $date->format($timeFormat)
                    : '';
                $year = $includeYear
                    ? ($includeMonthYearSeparator ? ' de ' : ' ') . $date->format('Y')
                    : '';

                return $weekDay
                    . $day
                    . self::getMonthName($date->format('n'), $lang, $shortMonthName)
                    . $year
                    . $time;
            default:
                $format = ($includeWeekDay ? 'l, ' : '')
                    . ($includeDay ? 'jS ' : '')
                    . ($shortMonthName ? 'M' : 'F')
                    . ($includeYear ? ' Y' : '')
                    . ($includeTime ? ' \a\t ' . $timeFormat : '');

                return $date->format($format);
        }
    }

    public static function getMonthName(int $month, string $lang = 'EN', bool $shorName = false): string
    {
        $lang = strtoupper($lang);
        $months = match($lang) {
            'ES' => ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto',
                'septiembre', 'octubre', 'noviembre', 'dieciembre'],
            'GL' => ['xaneiro', 'febreiro', 'marzo', 'abril', 'maio', 'xuño', 'xullo',
                'agosto', 'setembro', 'outubro', 'novembro', 'decembro'],
            default => ['January', 'February', 'March', 'April', 'May', 'June', 'July',
                'August', 'September', 'October', 'November', 'December'],
        };

        $month = $months[$month - 1] ?? '';

        return $shorName ? substr($month, 0, 3) : $month;
    }

    public static function isSummerTime(): bool
    {
        return date('I', time()) != 0;
    }

    public static function getTimezoneFromUtcOffset(int $offsetMinutes): string
    {
        return timezone_name_from_abbr('', $offsetMinutes * 60, 0);
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
        return $now->format(self::MYSQL_DATETIME_FORMAT);
    }

    public static function convertSecondsToDateInterval(int $seconds): DateInterval
    {
        $minutes = 0;
        $hours = 0;
        $days = 0;

        if ($seconds >= 60) {
            $minutes = round($seconds / 60, 0, PHP_ROUND_HALF_DOWN);
            $seconds = $seconds % 60;
        }

        if ($minutes >= 60) {
            $hours = round($minutes / 60, 0, PHP_ROUND_HALF_DOWN);
            $minutes = $minutes % 60;
        }

        if ($hours >= 24) {
            $days = round($hours / 24, 0, PHP_ROUND_HALF_DOWN);
            $hours = $hours % 24;
        }

        return new DateInterval(
            'P0000-00-'
            . str_pad($days, 2, '0', STR_PAD_LEFT)
            . 'T'
            . str_pad($hours, 2, '0', STR_PAD_LEFT)
            . ':'
            . str_pad($minutes, 2, '0', STR_PAD_LEFT)
            . ':'
            . str_pad($seconds, 2, '0', STR_PAD_LEFT)
        );
    }
}
