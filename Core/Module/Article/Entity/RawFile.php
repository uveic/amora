<?php

namespace Amora\Core\Module\Article\Entity;

use Amora\Core\Module\Article\Value\MediaType;

readonly class RawFile
{
    public function __construct(
        public string $originalName,
        public string $name,
        public string $basePath,
        public string $extraPath,
        public string $extension,
        public MediaType $mediaType,
        public ?int $sizeBytes = null,
        public ?int $error = null,
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
