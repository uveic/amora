<?php

namespace Amora\Core\Module\Article\Model;

use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class ArticlePath
{
    public function __construct(
        public ?int $id,
        public readonly int $articleId,
        public readonly string $path,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public static function fromArray(array $item): self
    {
        return new self(
            id: (int)$item['article_path_id'],
            articleId: (int)$item['article_path_article_id'],
            path: $item['article_path_path'],
            createdAt: DateUtil::convertStringToDateTimeImmutable(
                $item['article_path_created_at']
            ),
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'article_id' => $this->articleId,
            'path' => $this->path,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
        ];
    }

    public function asPublicArray(): array
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'createdAt' => $this->createdAt->format('c'),
        ];
    }
}
