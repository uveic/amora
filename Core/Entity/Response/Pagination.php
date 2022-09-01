<?php

namespace Amora\Core\Entity\Response;

class Pagination
{
    public function __construct(
        public readonly int $itemsPerPage = 10000,
        public readonly int $offset = 0,
    ) {}

    public function asArray(): array
    {
        return [
            'offset' => $this->offset,
            'itemsPerPage' => $this->itemsPerPage,
        ];
    }
}
