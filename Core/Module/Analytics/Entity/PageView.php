<?php

namespace Amora\Core\Module\Analytics\Entity;

use DateTimeImmutable;

class PageView
{
    public function __construct(
        public readonly int $count,
        public readonly DateTimeImmutable $date,
    ) {}
}
