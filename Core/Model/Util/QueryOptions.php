<?php

namespace Amora\Core\Model\Util;

class QueryOptions
{
    public function __construct(
        private array $orderBy = [],
        private int $limit = 10000,
        private int $offset = 0,
        private bool $orderRandomly = false,
    ) {}

    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function orderRandomly(): bool
    {
        return $this->orderRandomly;
    }
}
