<?php

namespace Amora\Core\Model\Util;

class QueryOptions
{
    public function __construct(
        private ?string $orderBy = null,
        private string $sortingDirection = 'DESC',
        private int $limit = 10000,
        private int $offset = 0,
    ) {
        $this->sortingDirection = strtoupper(trim($this->sortingDirection));
        if (!in_array($this->sortingDirection, ['ASC', 'DESC'])) {
            $this->sortingDirection = 'DESC';
        }
    }

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function getSortingDirection(): string
    {
        return $this->sortingDirection;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}
