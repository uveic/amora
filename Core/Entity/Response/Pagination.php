<?php

namespace Amora\Core\Entity\Response;

readonly class Pagination
{
    public function __construct(
        public int $itemsPerPage = 10000,
        public int $offset = 0,
    ) {}

    public function asArray(): array
    {
        return [
            'offset' => $this->offset,
            'itemsPerPage' => $this->itemsPerPage,
        ];
    }
}
