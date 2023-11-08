<?php

namespace Amora\Core\Util;

use Amora\App\Value\Language;
use Amora\Core\Value\AggregateBy;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Amora\Core\Core;
use DateTimeInterface;
use DateTimeZone;
use Exception;
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

        $dateObj = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $isoDate);
        if ($dateObj === false) {
            // Check if it is from Javascript and it contains milliseconds. Remove milliseconds if found.
            $isoDate = preg_replace(
                pattern: "/\.[0-9]{3}Z/",
                replacement: 'Z',
                subject: $isoDate,
            );

            $dateObj = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $isoDate);
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

    public static function getCurrentDateForMySql(): string
    {
        return date(self::MYSQL_DATETIME_FORMAT);
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
     * @param Language $language
     * @param bool $full
     * @param bool $includePrefixAndOrSuffix
     * @param bool $includeSeconds
     * @return string
     * @throws Exception
     */
    public static function getElapsedTimeString(
        Language $language,
        DateTimeImmutable|DateTime $from,
        DateTimeImmutable|DateTime|null $to = null,
        bool $full = false,
        bool $includePrefixAndOrSuffix = false,
        bool $includeSeconds = false,
    ): string {
        if (!isset($to)) {
            $to = new DateTimeImmutable(timezone: $from->getTimezone());
        }
        $diff = (array)$to->diff($from);

        switch ($language->value) {
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
                $value = $diff[$key]
                    . ' '
                    . $value
                    . ($diff[$key] > 1
                        ? ($key === 'm' && ($language === Language::Spanish || $language === Language::Galego) ? 'es' : 's')
                        : ''
                    );
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

    public static function convertStringToDateTimeImmutable(
        string $date,
        ?DateTimeZone $timezone = null,
    ): DateTimeImmutable {
        try {
            $d = new DateTime(datetime: $date);
            $timezone = $timezone ?? DateUtil::convertStringToDateTimeZone(Core::getDefaultTimezone());
            $d->setTimezone($timezone);

            return DateTimeImmutable::createFromMutable($d);
        } catch (Throwable) {
            Core::getDefaultLogger()->logError('Error converting string to DateTimeImmutable: ' . $date);
            return new DateTimeImmutable();
        }
    }

    public static function convertUnixTimestampToDateTimeImmutable(
        int $unixSeconds,
        ?DateTimeZone $timezone = null,
    ): DateTimeImmutable {
        try {
            $d = DateTime::createFromFormat(
                format: 'U',
                datetime: $unixSeconds,
            );
            $d->setTimezone($timezone ?? new DateTimeZone(Core::getDefaultTimezone()));

            return DateTimeImmutable::createFromMutable($d);
        } catch (Throwable) {
            Core::getDefaultLogger()->logError(
                'Error converting unix seconds to DateTimeImmutable: ' . $unixSeconds
            );
            return new DateTimeImmutable();
        }
    }

    public static function convertStringToDateTimeZone(string $timezone): DateTimeZone
    {
        try {
            return new DateTimeZone($timezone);
        } catch (Throwable) {
            Core::getDefaultLogger()->logError('Error converting string to DateTimeZone: ' . $timezone);
            return new DateTimeZone(Core::getDefaultTimezone());
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
        Language $lang,
        bool $includeDay = true,
        bool $includeMonth = true,
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

        switch ($lang->value) {
            case 'GL':
                $days = ['luns', 'martes', 'mércores', 'xoves', 'venres', 'sábado', 'domingo'];

                $weekDay = $includeWeekDay ? $days[$date->format('N') - 1] . ', ' : '';
                $day = $includeDay ? $date->format('j')
                    . ($includeDayMonthSeparator ? ' de ' : ' ')
                    : '';
                $month = $includeMonth
                    ? self::getMonthName($date->format('n'), $lang, $shortMonthName)
                    : '';
                $time = $includeTime
                    ? ($includeDayTimeSeparator ? ' ás ' : ' ') . $date->format($timeFormat)
                    : '';
                $year = $includeYear
                    ? ($includeMonthYearSeparator ? ' de ' : ' ') . $date->format('Y')
                    : '';

                return $weekDay . $day . $month . $year . $time;
            case 'ES':
                $days = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];

                $weekDay = $includeWeekDay ? $days[$date->format('N') - 1] . ', ' : '';
                $day = $includeDay ? $date->format('j')
                    . ($includeDayMonthSeparator ? ' de ' : ' ')
                    : '';
                $month = $includeMonth
                    ? self::getMonthName($date->format('n'), $lang, $shortMonthName)
                    : '';
                $time = $includeTime
                    ? ($includeDayTimeSeparator ? ' a las ' : ' ') . $date->format($timeFormat)
                    : '';
                $year = $includeYear
                    ? ($includeMonthYearSeparator ? ' de ' : ' ') . $date->format('Y')
                    : '';

                return $weekDay . $day . $month . $year . $time;
            default:
                $format = ($includeWeekDay ? 'l, ' : '')
                    . ($includeDay ? 'jS ' : '')
                    . ($includeMonth ? ($shortMonthName ? 'M' : 'F') : '')
                    . ($includeYear ? ' Y' : '')
                    . ($includeTime ? ' \a\t ' . $timeFormat : '');

                return $date->format($format);
        }
    }

    public static function getMonthName(int $month, Language $lang, bool $shorName = false): string
    {
        $months = match($lang->value) {
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

    public static function getMySqlAggregateFormat(AggregateBy $range): string
    {
        return match ($range) {
            AggregateBy::Minute => "'%Y-%m-%dT%H:%i'",
            AggregateBy::Hour => "'%Y-%m-%dT%H'",
            AggregateBy::Day => "'%Y-%m-%d'",
            AggregateBy::Week => "'%Y-%u'",
            AggregateBy::Month => "'%Y-%m'",
            AggregateBy::Year => "'%Y'",
        };
    }

    public static function getPhpAggregateFormat(AggregateBy $range): string
    {
        return match ($range) {
            AggregateBy::Minute => 'Y-m-d H:i',
            AggregateBy::Hour => 'Y-m-d H',
            AggregateBy::Day => 'Y-m-d',
            AggregateBy::Week => 'Y-W',
            AggregateBy::Month => 'Y-m',
            AggregateBy::Year => 'Y',
        };
    }

    public static function convertPartialDateFormatToFullDate(
        string $partialDate, // Format: yyyy-mm-dd
        AggregateBy $aggregatedBy,
        bool $roundUp = true,
    ): DateTimeImmutable {
        $res = match ($aggregatedBy) {
            AggregateBy::Minute => $partialDate . ($roundUp ? ':59' : ':00'),
            AggregateBy::Hour => $partialDate . ($roundUp ? ':59:59' : ':00:00'),
            AggregateBy::Day => $partialDate . ($roundUp ? ' 23:59:59' : ' 00:00:00'),
            default => null,
        };

        if ($res) {
            return self::convertStringToDateTimeImmutable($res);
        }

        if ($aggregatedBy === AggregateBy::Month) {
            $year = substr($partialDate, 0, 4);
            $month = substr($partialDate, 5, 2);

            if (!$roundUp) {
                return self::convertStringToDateTimeImmutable($year . '-' . $month . '-01 00:00:00');
            }

            $d = self::convertStringToDateTimeImmutable($year . '-' . $month . '-01 00:00:00');
            return self::convertStringToDateTimeImmutable(
                $year . '-' . $month . '-' . $d->format('t') . ' 23:59:59'
            );
        }

        if ($aggregatedBy === AggregateBy::Year) {
            $year = substr($partialDate, 0, 4);
            return self::convertStringToDateTimeImmutable(
                $year . ($roundUp ? '-12-31 23:59:59' : '-01-01 00:00:00')
            );
        }

        return new DateTimeImmutable();
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
