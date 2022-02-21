<?php

namespace Amora\Core\Module\Article\Model;

class ImagePath
{
    public function __construct(
        public readonly string $filePath,
        public readonly ?string $fullUrl = null
    ) {}
}
