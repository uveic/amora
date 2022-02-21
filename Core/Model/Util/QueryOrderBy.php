<?php

namespace Amora\Core\Model\Util;

class QueryOrderBy
{
    public function __construct(
        public readonly string $field,
        public readonly string $direction = 'DESC',
    ) {
        $this->direction = strtoupper(trim($this->direction));
        if (!in_array($this->direction, ['ASC', 'DESC', 'RAND()'])) {
            $this->direction = 'DESC';
        }
    }
}
