<?php

namespace Amora\Core\Module\Article\Model;

use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class MediaDestroyed
{
    public function __construct(
        public ?int $id,
        public readonly int $mediaId,
        public readonly ?string $fullPathWithName,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['media_destroyed_id'],
            mediaId: (int)$data['media_destroyed_media_id'],
            fullPathWithName: $data['media_destroyed_full_path_with_name'] ?? null,
            createdAt: DateUtil::convertStringToDateTimeImmutable($data['media_destroyed_created_at']),
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'media_id' => $this->mediaId,
            'full_path_with_name' => $this->fullPathWithName,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
        ];
    }
}
