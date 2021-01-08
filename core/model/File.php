<?php

namespace uve\core\model;

class File
{
    private string $name;
    private string $fullPath;
    private string $size;
    private string $type;
    private int $error;

    public function __construct(
        string $name,
        string $fullPath,
        string $size,
        string $type,
        int $error
    ) {
        $this->name = $name;
        $this->fullPath = $fullPath;
        $this->size = $size;
        $this->type = $type;
        $this->error = $error;
    }

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
