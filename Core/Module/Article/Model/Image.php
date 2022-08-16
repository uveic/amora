<?php

namespace Amora\Core\Module\Article\Model;

use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class Image
{
    public function __construct(
        public ?int $id,
        public readonly ?int $userId,
        public readonly string $fullUrlOriginal,
        public readonly ?string $fullUrlMedium,
        public readonly ?string $fullUrlLarge,
        public readonly string $filePathOriginal,
        public readonly ?string $filePathMedium,
        public readonly ?string $filePathLarge,
        public readonly ?string $caption,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt
    ) {}

    public static function fromArray(array $image): Image
    {
        return new Image(
            id: (int)$image['image_id'],
            userId: isset($image['image_user_id']) ? (int)$image['image_user_id'] : null,
            fullUrlOriginal: $image['full_url_original'],
            fullUrlMedium: $image['full_url_medium'] ?? null,
            fullUrlLarge: $image['full_url_large'] ?? null,
            filePathOriginal: $image['file_path_original'],
            filePathMedium: $image['file_path_medium'] ?? null,
            filePathLarge: $image['file_path_large'] ?? null,
            caption: $image['caption'] ?? null,
            createdAt: DateUtil::convertStringToDateTimeImmutable($image['image_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($image['image_updated_at']),
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'full_url_original' => $this->fullUrlOriginal,
            'full_url_medium' => $this->fullUrlMedium,
            'full_url_large' => $this->fullUrlLarge,
            'file_path_original' => $this->filePathOriginal,
            'file_path_medium' => $this->filePathMedium,
            'file_path_large' => $this->filePathLarge,
            'caption' => $this->caption,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
        ];
    }

    public function getFullUrlMedium(): ?string
    {
        return $this->fullUrlMedium ?? ($this->fullUrlLarge ?? $this->fullUrlOriginal);
    }

    public function buildPublicDataArray(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->getFullUrlMedium(),
            'caption' => $this->caption,
        ];
    }
}
