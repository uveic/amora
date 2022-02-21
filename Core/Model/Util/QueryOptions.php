<?php

namespace Amora\Core\Model\Util;

use Amora\Core\Model\Response\Pagination;

class QueryOptions
{
    public function __construct(
        public readonly array $orderBy = [],
        public readonly ?Pagination $pagination = null,
        public readonly bool $orderRandomly = false,
    ) {}

    public function getItemsPerPage(): int
    {
        return $this->pagination
            ? $this->pagination->itemsPerPage
            : 10000;
    }

    public function getOffset(): int
    {
        return $this->pagination
            ? $this->pagination->offset
            : 0;
    }
}
