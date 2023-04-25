<?php

namespace Amora\App\Module\Analytics\Entity;

use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Module\Analytics\Value\Period;
use Amora\Core\Value\AggregateBy;
use DateTimeImmutable;

class ReportViewCount
{
    public function __construct(
        public readonly DateTimeImmutable $from,
        public readonly DateTimeImmutable $to,
        public readonly AggregateBy $aggregateBy,
        public readonly Period $period,
        public readonly array $pageViews = [],
        public readonly int $total = 0,
        public readonly ?EventType $eventType = null,
    ) {}
}