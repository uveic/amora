<?php

namespace Amora\Core\Util;

use DateTime;
use DateTimeZone;
use Exception;
use Amora\Core\Core;

final class DateUtil
{

    /**
     * Check if the date is a valid ISO8601 date: YYYY-mm-ddTHH:mm:ssZ
     * https://xml2rfc.tools.ietf.org/public/rfc/html/rfc3339.html#anchor14
     * @param string $isoDate
     * @return bool
     */
    public static function isValidDateISO8601(string $isoDate): bool
    {
        $tmpDate = DateTime::createFromFormat("Y-m-d\TH:i:s\Z", $isoDate);
        if ($tmpDate === false) {
            $tmpDate = DateTime::createFromFormat("Y-m-d\TH:i:s\+H:i", $isoDate);
            if ($tmpDate === false) {
                return false;
            }
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

    /**
     * Check if the date is a valid for MySQL: Y-m-d H:i:s
     * @param string $date
     * @return bool
     */
    public static function isValidDateForMySql(string $date): bool
    {
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
     * @return string
     * @throws Exception
     */
    public static function getElapsedTimeString(
        string $datetime,
        string $language = 'EN',
        bool $full = false,
        bool $includePrefixAndOrSuffix = false,
        bool $includeSeconds = false
    ): string {
        $now = new DateTime;
        $ago = new DateTime($datetime);
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

        if ($diff['y'] !== 0
            && $diff['m'] !== 0
            && $diff['d'] !== 0
            && $diff['h'] !== 0
            && $diff['i'] !== 0
            && !$includeSeconds
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

    public static function formatDateFromUnixTime(
        ?int $unixTime = null,
        string $lang = 'EN',
        bool $includeWeekDay = true,
        bool $includeTime = false,
        string $timeZone = 'UTC'
    ): string {
        if (!isset($unixTime)) {
            $unixTime = time();
        }

        if (self::isSummerTime()) {
            $unixTime += 3600; // 1 hour
        }

        return self::formatUtcDate(
            self::getMySqlDateFromUnixTime($unixTime),
            $lang,
            $includeWeekDay,
            $includeTime,
            $timeZone
        );
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

        $d = new DateTime($stringDate, new DateTimeZone('UTC'));
        $d->setTimezone(new DateTimeZone($timezone));

        $format = 'd/m/Y';
        $format .= $includeTime
            ? ' H:i' . ($includeSeconds ? ':s' : '')
            : '';

        return $d->format($format);
    }

    public static function formatUtcDate(
        ?string $stringDate = null,
        string $lang = 'EN',
        bool $includeWeekDay = true,
        bool $includeTime = false,
        string $timezone = 'UTC',
        bool $includeYear = true
    ): string {
        if (!isset($stringDate)) {
            $stringDate = 'now';
        }

        $d = new DateTime($stringDate, new DateTimeZone('UTC'));
        $d->setTimezone(new DateTimeZone($timezone));

        $lang = strtoupper($lang);
        switch (strtoupper($lang)) {
            case 'GL':
                $months = ['xaneiro', 'febreiro', 'marzo', 'abril', 'maio', 'xuño', 'xullo',
                    'agosto', 'setembro', 'outubro', 'novembro', 'decembro'];
                $days = ['luns', 'martes', 'mércores', 'xoves', 'venres', 'sábado', 'domingo'];

                $weekDay = $includeWeekDay ? $days[$d->format('N') - 1] . ', ' : '';
                $time = $includeTime ? ' ás ' . $d->format('H:i') : '';
                $year = $includeYear ? ' de ' . $d->format('Y') : '';

                return $weekDay
                    . $d->format('j')
                    . ' de '
                    . $months[$d->format('n') - 1]
                    . $year
                    . $time;
            case 'ES':
                $months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto',
                    'septiembre', 'octubre', 'noviembre', 'dieciembre'];
                $days = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];

                $weekDay = $includeWeekDay ? $days[$d->format('N') - 1] . ', ' : '';
                $time = $includeTime ? ' a las ' . $d->format('H:i') : '';
                $year = $includeYear ? ' de ' . $d->format('Y') : '';

                return $weekDay
                    . $d->format('j')
                    . ' de '
                    . $months[$d->format('n') - 1]
                    . $year
                    . $time;
            default:
                $format = ($includeWeekDay ? 'l, ' : '')
                    . 'F jS'
                    . ($includeYear ? ', Y' : '')
                    . ($includeTime ? ' \a\t H:i' : '');

                return $d->format($format);
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
        $d = new DateTime($stringDate, new DateTimeZone('UTC'));
        $d->setTimezone(new DateTimeZone($timezone));
        return $d->format($outputFormat);
    }
}
