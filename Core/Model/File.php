<?php

namespace Amora\Core\Model;

class File
{
    public function __construct(
        private string $name,
        private string $fullPath,
        private string $size,
        private string $type,
        private int $error
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getError(): int
    {
        return $this->error;
    }
}
