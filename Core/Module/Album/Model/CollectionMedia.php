<?php

namespace Amora\Core\Module\Album\Model;

use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class CollectionMedia
{
    public function __construct(
        public ?int $id,
        public readonly int $collectionId,
        public readonly Media $media,
        public readonly ?string $captionHtml,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly int $sequence,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['collection_media_id']) ? (int)$data['collection_media_id'] : null,
            collectionId: (int)$data['collection_media_collection_id'],
            media: Media::fromArray($data),
            captionHtml: $data['collection_media_caption_html'] ?? null,
            createdAt: DateUtil::convertStringToDateTimeImmutable($data['collection_media_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($data['collection_media_updated_at']),
            sequence: (int)$data['collection_media_sequence'],
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'collection_id' => $this->collectionId,
            'media_id' => $this->media->id,
            'caption_html' => $this->captionHtml,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'sequence' => $this->sequence,
        ];
    }

    public function buildAltText(): string
    {
        $html = $this->media->captionHtml;
        if (empty($html)) {
            return '';
        }

        return strip_tags($html);
    }
}
