<?php

namespace Amora\Core\Module\Article\Entity;

use Amora\Core\Module\Article\Value\MediaType;

class RawFile
{
    public function __construct(
        public readonly string $originalName,
        public readonly string $name,
        public readonly string $path,
        public readonly string $extension,
        public readonly MediaType $mediaType,
        public readonly ?int $sizeBytes = null,
        public readonly ?int $error = null,
    ) {}

    public function getPathWithName(): string
    {
        return rtrim($this->path, '/ ') . '/' . $this->name;
    }
}
