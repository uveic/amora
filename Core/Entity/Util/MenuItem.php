<?php

namespace Amora\Core\Entity\Util;

readonly class MenuItem
{
    public function __construct(
        public ?string $path = null,
        public ?string $text = null,
        public ?string $icon = null,
        public array $children = [],
        public int $sequence = 0,
        public ?string $class = null,
        public array $dataset = [],
    ) {
    }
}
