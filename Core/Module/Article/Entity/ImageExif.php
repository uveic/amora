<?php

namespace Amora\Core\Module\Article\Entity;

use DateTimeImmutable;

readonly class ImageExif
{
    public function __construct(
        public ?int $width,
        public ?int $height,
        public ?int $sizeBytes,
        public ?string $cameraModel,
        public ?DateTimeImmutable $date,
        public ?string $exposureTime,
        public ?string $ISO,
        public ?string $rawDataJson = null,
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
