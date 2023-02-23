<?php

namespace Amora\Core\Module\Article\Entity;

use Amora\Core\Module\Article\Value\MediaType;

class RawFile
{
    public function __construct(
        public readonly string $originalName,
        public readonly string $name,
        public readonly string $basePath,
        public readonly string $extraPath,
        public readonly string $extension,
        public readonly MediaType $mediaType,
        public readonly ?int $sizeBytes = null,
        public readonly ?int $error = null,
    ) {}

    public function getPath(): string
    {
        return rtrim(rtrim($this->basePath, '/ ') . '/' . $this->extraPath, ' /');
    }

    public function getPathWithName(): string
    {
        return $this->getPath() . '/' . $this->name;
    }
}
