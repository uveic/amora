<?php

namespace Amora\Core\Module\Album\Model;

use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class AlbumSection
{
    public function __construct(
        public ?int $id,
        public readonly int $albumId,
        public readonly Media $mainMedia,
        public readonly ?string $titleHtml,
        public readonly ?string $contentHtml,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['album_section_id']) ? (int)$data['album_section_id'] : null,
            albumId: (int)$data['album_id'],
            mainMedia: Media::fromArray($data),
            titleHtml: $data['album_section_title_html'] ?? null,
            contentHtml: $data['album_section_content_html'],
            createdAt: DateUtil::convertStringToDateTimeImmutable($data['album_section_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($data['album_section_updated_at']),
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'album_id' => $this->albumId,
            'main_media_id' => $this->mainMedia->id,
            'title_html' => $this->titleHtml,
            'content_html' => $this->contentHtml,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
        ];
    }
}
