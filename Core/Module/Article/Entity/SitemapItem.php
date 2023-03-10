<?php

namespace Amora\Core\Module\Article\Entity;

use DateTimeImmutable;

class SitemapItem
{
    public function __construct(
        public readonly string $fullPath,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}
}
