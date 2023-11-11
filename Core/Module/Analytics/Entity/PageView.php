<?php

namespace Amora\Core\Module\Analytics\Entity;

use DateTimeImmutable;

readonly class PageView
{
    public function __construct(
        public int $count,
        public DateTimeImmutable $date,
    ) {}
}
