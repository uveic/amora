<?php

namespace Amora\Core\Value;

use DateInterval;

enum AggregateBy: string
{
    case Minute = 'minute';
    case Hour = 'hour';
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';

    public function getInterval(): DateInterval
    {
        return match ($this) {
            self::Minute => new DateInterval('P0000-00-00T00:01:00'),
            self::Hour => new DateInterval('P0000-00-00T01:00:00'),
            self::Day => new DateInterval('P0000-00-01T00:00:00'),
            self::Week => new DateInterval('P0000-00-07T00:00:00'),
            self::Month => new DateInterval('P0000-01-00T00:00:00'),
            self::Year => new DateInterval('P0001-00-00T00:00:00'),
        };
    }

    public function getDateFormat(): string
    {
        return match ($this) {
            self::Minute => 'Y-m-d-H-i',
            self::Hour => 'Y-m-d-H',
            self::Day => 'Y-m-d',
            self::Week => 'Y-W',
            self::Month => 'Y-m',
            self::Year => 'Y',
        };
    }
}
