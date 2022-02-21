<?php

namespace Amora\Core\Model\Util;

use Amora\Core\Model\Response\Pagination;

class QueryOptions
{
    public function __construct(
        public readonly array $orderBy = [],
        public readonly ?Pagination $pagination = null,
        public readonly bool $orderRandomly = false,
    ) {
        $this->pagination = $this->pagination ?? new Pagination();
    }

    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    public function getPagination(): Pagination
    {
        return $this->pagination;
    }

    public function getLimit(): int
    {
        return $this->pagination->itemsPerPage;
    }

    public function getOffset(): int
    {
        return $this->pagination->offset;
    }

    public function orderRandomly(): bool
    {
        return $this->orderRandomly;
    }
}
