<?php

namespace Amora\Core\Module\Analytics\Entity;

readonly class PageViewCount
{
    public function __construct(
        public int $count,
        public int $id,
        public string $value,
    ) {}
}
