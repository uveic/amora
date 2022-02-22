<?php

namespace Amora\Core\Menu;

class MenuItem
{
    public function __construct(
        public readonly ?string $uri = null,
        public readonly ?string $text = null,
        public readonly ?string $icon = null,
        public readonly array $children = [],
        public readonly int $order = 0,
    ) {}
}
