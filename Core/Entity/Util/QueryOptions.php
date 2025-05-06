<?php

namespace Amora\Core\Entity\Util;

use Amora\Core\Entity\Response\Pagination;

readonly class QueryOptions
{
    public function __construct(
        public array $orderBy = [],
        public ?Pagination $pagination = null,
        public bool $orderRandomly = false,
    ) {}

    public function getItemsPerPage(): int
    {
        return $this->pagination
            ? $this->pagination->itemsPerPage
            : 100000;
    }

    public function getOffset(): int
    {
        return $this->pagination
            ? $this->pagination->offset
            : 0;
    }
}
