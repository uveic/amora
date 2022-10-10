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

    public static function getInterval(self $item): DateInterval
    {
        return match ($item) {
            AggregateBy::Minute => new DateInterval('P0000-00-00T00:01:00'),
            AggregateBy::Hour => new DateInterval('P0000-00-00T01:00:00'),
            AggregateBy::Day => new DateInterval('P0000-00-01T00:00:00'),
            AggregateBy::Week => new DateInterval('P0000-00-07T00:00:00'),
            AggregateBy::Month => new DateInterval('P0000-01-00T00:00:00'),
            AggregateBy::Year => new DateInterval('P0001-00-00T00:00:00'),
        };
    }
}
