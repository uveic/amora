<?php

namespace Amora\Core\Module\Analytics\Value;

use Amora\Core\Util\DateUtil;
use Amora\Core\Value\AggregateBy;
use DateTimeImmutable;

enum Period: string
{
    case Last30Days = '30d';
    case Last7Days = '7d';
    case Day = 'day';
    case Month = 'month';
    case Year = 'year';

    public static function getFrom(
        self $item,
        ?string $date,
    ): DateTimeImmutable {
        return match($item) {
            self::Last30Days => DateUtil::convertPartialDateFormatToFullDate(
                partialDate: (new DateTimeImmutable('-30 days'))->format('Y-m-d'),
                aggregatedBy: AggregateBy::Day,
                roundUp: false,
            ),
            self::Last7Days => DateUtil::convertPartialDateFormatToFullDate(
                partialDate: (new DateTimeImmutable('-7 days'))->format('Y-m-d'),
                aggregatedBy: AggregateBy::Day,
                roundUp: false,
            ),
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
        $now = new DateTimeImmutable();

        return match ($item) {
            self::Last7Days, self::Last30Days => DateUtil::convertPartialDateFormatToFullDate(
                partialDate: $now->format('Y-m-d'),
                aggregatedBy: AggregateBy::Day,
            ),
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
            self::Last30Days, self::Month,
            self::Last7Days => AggregateBy::Day,
            self::Day => AggregateBy::Hour,
            self::Year => AggregateBy::Month,
        };
    }
}
