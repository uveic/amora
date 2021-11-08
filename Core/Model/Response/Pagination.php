<?php

namespace Amora\Core\Model\Response;

class Pagination
{
    public function __construct(
        private int $itemsPerPage = 10000,
        private int $offset = 0,
    ) {}

    public function asArray(): array
    {
        return [
            'offset' => $this->getOffset(),
            'itemsPerPage' => $this->getItemsPerPage(),
        ];
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}
