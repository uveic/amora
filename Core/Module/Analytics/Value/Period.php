<?php

namespace Amora\Core\Module\Analytics\Value;

use Amora\Core\Util\DateUtil;
use Amora\Core\Value\AggregateBy;
use DateTimeImmutable;

enum Period: int
{
    case Day = 1;
    case Month = 2;
    case Year = 3;

    public static function getAll(): array
    {
        return [
            self::Day,
            self::Month,
            self::Year,
        ];
    }

    public function getFrom(string $date): DateTimeImmutable
    {
        return match($this) {
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

    public function getTo(DateTimeImmutable $from): DateTimeImmutable
    {
        return match ($this) {
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

    public function getAggregateBy(): AggregateBy
    {
        return match($this) {
            self::Day => AggregateBy::Hour,
            self::Month => AggregateBy::Day,
            self::Year => AggregateBy::Month,
        };
    }

    public function getName(): string
    {
        return match($this) {
            self::Day => 'day',
            self::Month => 'month',
            self::Year => 'year',
        };
    }

    public static function getFromString(?string $name): self
    {
        return match($name) {
            'month' => self::Month,
            'year' => self::Year,
            default => self::Day,
        };
    }
}
