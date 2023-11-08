<?php

namespace Amora\Core\Module\Album\Model;

use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class AlbumSlug
{
    public function __construct(
        public ?int $id,
        public readonly ?int $albumId,
        public readonly string $slug,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public static function fromArray(array $item): self
    {
        return new self(
            id: (int)$item['album_slug_id'],
            albumId: isset($item['album_slug_album_id']) ? (int)$item['album_slug_album_id'] : null,
            slug: $item['album_slug_slug'],
            createdAt: DateUtil::convertStringToDateTimeImmutable(
                $item['album_slug_created_at'],
            ),
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'album_id' => $this->albumId,
            'slug' => $this->slug,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
        ];
    }
}
