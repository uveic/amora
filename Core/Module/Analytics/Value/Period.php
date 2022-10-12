<?php

namespace Amora\Core\Module\Analytics\Value;

use Amora\Core\Util\DateUtil;
use Amora\Core\Value\AggregateBy;
use DateTimeImmutable;

enum Period: string
{
    case Day = 'day';
    case Month = 'month';
    case Year = 'year';

    public static function getFrom(
        self $item,
        string $date,
    ): DateTimeImmutable {
        return match($item) {
            self::Day => DateUtil::convertPartialDateFormatToFullDate(
                partialDate: $date,
                aggregatedBy: AggregateBy::Day,
                roundUp: false,
            ),
            self::Month => DateUtil::convertPartialDateFormatToFullDate(
                partialDate: $date,
                aggregatedBy: AggregateBy::Month,
                roundUp: false,
            ),
            self::Year => DateUtil::convertPartialDateFormatToFullDate(
                partialDate: $date,
                aggregatedBy: AggregateBy::Year,
                roundUp: false,
            ),
        };
    }

    public static function getTo(
        self $item,
        DateTimeImmutable $from,
    ): DateTimeImmutable {
        return match ($item) {
            self::Day => DateUtil::convertPartialDateFormatToFullDate(
                partialDate: $from->format('Y-m-d'),
                aggregatedBy: AggregateBy::Day,
            ),
            self::Month => DateUtil::convertPartialDateFormatToFullDate(
                partialDate: $from->format('Y-m-d'),
                aggregatedBy: AggregateBy::Month,
            ),
            self::Year => DateUtil::convertPartialDateFormatToFullDate(
                partialDate: $from->format('Y-m-d'),
                aggregatedBy: AggregateBy::Year,
            ),
        };
    }

    public static function getAggregateBy(self $item): AggregateBy
    {
        return match($item) {
            self::Day => AggregateBy::Hour,
            self::Month => AggregateBy::Day,
            self::Year => AggregateBy::Month,
        };
    }
}
