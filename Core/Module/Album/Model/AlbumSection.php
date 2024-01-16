<?php

namespace Amora\Core\Module\Album\Model;

use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;
use DateTimeImmutable;

class AlbumSection
{
    public function __construct(
        public ?int $id,
        public readonly int $albumId,
        public readonly ?Media $mainMedia,
        public readonly ?string $titleHtml,
        public readonly ?string $subtitleHtml,
        public readonly ?string $contentHtml,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly int $sequence,
        public readonly array $media = [],
    ) {}

    public static function fromArray(array $data, array $media = []): self
    {
        return new self(
            id: isset($data['album_section_id']) ? (int)$data['album_section_id'] : null,
            albumId: (int)$data['album_section_album_id'],
            mainMedia: isset($data['media_id']) ? Media::fromArray($data) : null,
            titleHtml: $data['album_section_title_html'] ?? null,
            subtitleHtml: $data['album_section_subtitle_html'] ?? null,
            contentHtml: $data['album_section_content_html'] ?? null,
            createdAt: DateUtil::convertStringToDateTimeImmutable($data['album_section_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($data['album_section_updated_at']),
            sequence: (int)$data['album_section_sequence'],
            media: $media,
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'album_id' => $this->albumId,
            'main_media_id' => $this->mainMedia?->id,
            'title_html' => $this->titleHtml,
            'subtitle_html' => $this->subtitleHtml,
            'content_html' => $this->contentHtml,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'sequence' => $this->sequence,
        ];
    }

    public function buildUniqueSlug(): string
    {
        return strtolower(
            StringUtil::cleanString(
                $this->sequence . '-' . $this->titleHtml . '-' . $this->subtitleHtml
            )
        );
    }
}
