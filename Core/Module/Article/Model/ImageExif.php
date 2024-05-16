<?php

namespace Amora\Core\Module\Article\Model;

use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

readonly class ImageExif
{
    public function __construct(
        public ?int $width,
        public ?int $height,
        public ?int $sizeBytes,
        public ?string $cameraModel,
        public ?DateTimeImmutable $takenAt,
        public ?string $exposureTime,
        public ?string $iso,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            width: isset($data['media_exif_width']) ? (int)$data['media_exif_width'] : null,
            height: isset($data['media_exif_height']) ? (int)$data['media_exif_height'] : null,
            sizeBytes: isset($data['media_exif_size_bytes']) ? (int)$data['media_exif_size_bytes'] : null,
            cameraModel: $data['media_exif_camera_model'] ?? null,
            takenAt: isset($data['media_exif_taken_at']) ?
                DateUtil::convertStringToDateTimeImmutable($data['media_exif_taken_at'])
                : null,
            exposureTime: $data['media_exif_exposure_time'] ?? null,
            iso: $data['media_exif_iso'] ?? null,
        );
    }

    public function asArray(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'size_bytes' => $this->sizeBytes,
            'camera_model' => $this->cameraModel,
            'taken_at' => $this->takenAt?->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'exposure_time' => $this->exposureTime,
            'iso' => $this->iso,
        ];
    }

    public function asPublicArray(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'sizeBytes' => $this->sizeBytes,
            'cameraModel' => $this->cameraModel,
            'date' => $this->takenAt?->format('c'),
            'exposureTime' => $this->exposureTime,
            'ISO' => $this->iso,
        ];
    }

    public function isEmpty(): bool
    {
        return !$this->width &&
            !$this->height &&
            !isset($this->sizeBytes) &&
            !$this->cameraModel &&
            !$this->takenAt &&
            !$this->exposureTime &&
            !$this->iso;
    }
}
