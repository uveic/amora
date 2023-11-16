<?php

namespace Amora\Core\Module\Album\Model;

use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class AlbumSectionMedia
{
    public function __construct(
        public ?int $id,
        public readonly int $albumSectionId,
        public readonly Media $media,
        public readonly ?string $titleHtml,
        public readonly ?string $contentHtml,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly int $sequence,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['album_section_media_id']) ? (int)$data['album_section_media_id'] : null,
            albumSectionId: (int)$data['album_section_media_album_section_id'],
            media: Media::fromArray($data),
            titleHtml: $data['album_section_media_title_html'] ?? null,
            contentHtml: $data['album_section_media_content_html'],
            createdAt: DateUtil::convertStringToDateTimeImmutable($data['album_section_media_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($data['album_section_media_updated_at']),
            sequence: (int)$data['album_section_media_sequence'],
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'album_section_id' => $this->albumSectionId,
            'media_id' => $this->media->id,
            'title_html' => $this->titleHtml,
            'content_html' => $this->contentHtml,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'sequence' => $this->sequence,
        ];
    }
}
