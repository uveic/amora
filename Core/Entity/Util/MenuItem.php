<?php

namespace Amora\Core\Entity\Util;

class MenuItem
{
    public function __construct(
        public readonly ?string $uri = null,
        public readonly ?string $text = null,
        public readonly ?string $icon = null,
        public readonly array $children = [],
        public readonly int $order = 0,
        public readonly ?string $class = null,
    ) {}
}
