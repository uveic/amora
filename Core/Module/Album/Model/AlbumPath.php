<?php

namespace Amora\Core\Module\Album\Model;

use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class AlbumPath
{
    public function __construct(
        public ?int $id,
        public readonly int $albumId,
        public readonly string $path,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public static function fromArray(array $item): self
    {
        return new self(
            id: (int)$item['album_path_id'],
            albumId: (int)$item['album_path_album_id'],
            path: $item['album_path_path'],
            createdAt: DateUtil::convertStringToDateTimeImmutable(
                $item['album_path_created_at'],
            ),
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'album_id' => $this->albumId,
            'path' => $this->path,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
        ];
    }
}
