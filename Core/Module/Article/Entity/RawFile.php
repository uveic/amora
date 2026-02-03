<?php

namespace Amora\Core\Module\Article\Entity;

use Amora\Core\Core;
use Amora\Core\Module\Article\Value\MediaType;

readonly class RawFile
{
    public function __construct(
        public string $originalName,
        public string $baseNameWithoutExtension,
        public string $extension,
        public string $basePath,
        public string $extraPath,
        public MediaType $mediaType,
        public ?int $sizeBytes = null,
        public ?int $error = null,
    ) {
    }

    public function getName(): string
    {
        return $this->baseNameWithoutExtension . '.' . $this->extension;
    }

    public function getPath(): string
    {
        return rtrim(rtrim($this->basePath, '/ ') . '/' . $this->extraPath, ' /');
    }

    public function getPathWithName(): string
    {
        return $this->getPath() . '/' . $this->getName();
    }

    public function getPublicPathWithName(): string
    {
        return Core::getConfig()->mediaBaseUrl .
            '/' .
            ($this->extraPath ? $this->extraPath . '/' : '') .
            $this->getName();
    }
}
