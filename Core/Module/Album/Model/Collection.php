<?php

namespace Amora\Core\Module\Album\Model;

use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;
use DateTimeImmutable;

class Collection
{
    public function __construct(
        public ?int $id,
        public readonly ?int $albumId,
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
            id: isset($data['collection_id']) ? (int)$data['collection_id'] : null,
            albumId: isset($data['collection_album_id']) ? (int)$data['collection_album_id'] : null,
            mainMedia: isset($data['media_id']) ? Media::fromArray($data) : null,
            titleHtml: $data['collection_title_html'] ?? null,
            subtitleHtml: $data['collection_subtitle_html'] ?? null,
            contentHtml: $data['collection_content_html'] ?? null,
            createdAt: DateUtil::convertStringToDateTimeImmutable($data['collection_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($data['collection_updated_at']),
            sequence: (int)$data['collection_sequence'],
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
        $text = $this->titleHtml ?: '';
        if ($this->subtitleHtml) {
            $text .= $this->subtitleHtml;
        }

        if (!$text) {
            $text = StringUtil::generateRandomString(10);
        }

        return strtolower(substr(StringUtil::cleanString('co' . $this->sequence . $text), 0, 16));
    }

    public static function getEmpty(): self
    {
        $now = new DateTimeImmutable();

        return new self(
            id: null,
            albumId: null,
            mainMedia: null,
            titleHtml: null,
            subtitleHtml: null,
            contentHtml: null,
            createdAt: $now,
            updatedAt: $now,
            sequence: 0,
        );
    }
}
