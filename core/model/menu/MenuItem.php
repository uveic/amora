<?php

namespace uve\Core\Menu;

class MenuItem
{
    public function __construct(
        private string $uri,
        private ?string $text = null,
        private ?string $icon = null,
        private ?string $parent = null,
        private bool $isForAdmin = false,
    ) {}

    public function getUri(): string
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

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function isForAdmin(): bool
    {
        return $this->isForAdmin;
    }
}
