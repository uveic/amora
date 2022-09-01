<?php

namespace Amora\Core\Entity\Util;

use Amora\Core\Value\QueryOrderDirection;

class QueryOrderBy
{
    public function __construct(
        public readonly string $field,
        public readonly QueryOrderDirection $direction,
    ) {}
}
