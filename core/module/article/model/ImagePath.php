<?php

namespace uve\core\module\article\model;

class ImagePath
{
    private string $filePath;
    private ?string $fullUrl;

    public function __construct(
        string $filePath,
        ?string $fullUrl = null
    ) {
        $this->fullUrl = $fullUrl;
        $this->filePath = $filePath;
    }

    public function getFullUrl(): ?string
    {
        return $this->fullUrl;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
