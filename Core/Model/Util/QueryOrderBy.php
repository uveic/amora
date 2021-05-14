<?php

namespace Amora\Core\Model\Util;

class QueryOrderBy
{
    public function __construct(
        private string $field,
        private string $direction = 'DESC',
    ) {
        $this->direction = strtoupper(trim($this->direction));
        if (!in_array($this->direction, ['ASC', 'DESC', 'RAND()'])) {
            $this->direction = 'DESC';
        }
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }
}
