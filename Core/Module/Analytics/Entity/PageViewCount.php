<?php

namespace Amora\Core\Module\Analytics\Entity;

class PageViewCount
{
    public function __construct(
        public readonly int $count,
        public readonly string $name,
    ) {}
}
