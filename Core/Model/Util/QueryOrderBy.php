<?php

namespace Amora\Core\Model\Util;

use Amora\Core\Value\QueryOrderDirection;

class QueryOrderBy
{
    public function __construct(
        public readonly string $field,
        public readonly QueryOrderDirection $direction,
    ) {}
}
