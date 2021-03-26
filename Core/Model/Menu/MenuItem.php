<?php

namespace Amora\Core\Menu;

class MenuItem
{
    public function __construct(
        private ?string $uri = null,
        private ?string $text = null,
        private ?string $icon = null,
        private array $children = [],
        private int $order = 0,
    ) {}

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getChildren(): array
    {
        return $this->children ?? [];
    }

    public function getOrder(): int
    {
        return $this->order;
    }
}
