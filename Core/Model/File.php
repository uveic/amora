<?php

namespace Amora\Core\Model;

class File
{
    public function __construct(
        public readonly string $name,
        public readonly string $fullPath,
        public readonly ?string $size = null,
        public readonly ?string $type = null,
        public readonly ?int $error = null,
    ) {}

    public function getFullPathWithName(): string
    {
        return $this->fullPath . $this->name;
    }

    public function getExtension(): string
    {
        if (!str_contains($this->name, '.')) {
            return '';
        }

        $parts = explode('.', $this->name);
        return strtolower(trim($parts[count($parts) - 1]));
    }
}
