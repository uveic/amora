<?php

namespace Amora\Core\Module\Article\Entity;

use DateTimeImmutable;

class ImageExif
{
    public function __construct(
        public readonly ?int $width,
        public readonly ?int $height,
        public readonly ?int $sizeBytes,
        public readonly ?string $cameraModel,
        public readonly ?DateTimeImmutable $date,
        public readonly ?string $exposureTime,
        public readonly ?string $ISO,
        public readonly ?string $rawDataJson = null,
    ) {}

    public function asArray(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'sizeBytes' => $this->sizeBytes,
            'cameraModel' => $this->cameraModel,
            'date' => $this->date?->format('c'),
            'exposureTime' => $this->exposureTime,
            'ISO' => $this->ISO,
        ];
    }
}
