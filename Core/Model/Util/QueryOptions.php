<?php

namespace Amora\Core\Model\Util;

use Amora\Core\Model\Response\Pagination;

class QueryOptions
{
    public function __construct(
        private array $orderBy = [],
        private ?Pagination $pagination = null,
        private bool $orderRandomly = false,
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
        return $this->getPagination()->getItemsPerPage();
    }

    public function getOffset(): int
    {
        return $this->getPagination()->getOffset();
    }

    public function orderRandomly(): bool
    {
        return $this->orderRandomly;
    }
}
