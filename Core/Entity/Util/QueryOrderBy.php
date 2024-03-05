<?php

namespace Amora\Core\Entity\Util;

use Amora\Core\Value\QueryOrderDirection;

readonly class QueryOrderBy
{
    public function __construct(
        public string $field,
        public QueryOrderDirection $direction,
    ) {}
}
