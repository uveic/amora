<?php

namespace Amora\App\Module\Analytics\Entity;

use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Module\Analytics\Value\Parameter;
use Amora\Core\Module\Analytics\Value\Period;
use Amora\Core\Value\AggregateBy;
use DateTimeImmutable;

readonly class ReportViewCount
{
    public function __construct(
        public DateTimeImmutable $from,
        public DateTimeImmutable $to,
        public AggregateBy $aggregateBy,
        public Period $period,
        public array $pageViews = [],
        public int $total = 0,
        public ?EventType $eventType = null,
        public ?Parameter $parameter = null,
    ) {
    }
}
