<?php

namespace Amora\App\Module\Analytics\Entity;

use Amora\Core\Value\AggregateBy;
use DateTimeImmutable;

class ReportPageView
{
    public function __construct(
        public readonly DateTimeImmutable $from,
        public readonly DateTimeImmutable $to,
        public readonly AggregateBy $aggregateBy,
        public readonly array $pageViews = [],
        public readonly int $total = 0,
    ) {}
}
