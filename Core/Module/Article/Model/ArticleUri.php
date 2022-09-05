<?php

namespace Amora\Core\Module\Article\Model;

use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class ArticleUri
{
    public function __construct(
        public ?int $id,
        public readonly int $articleId,
        public readonly string $uri,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public static function fromArray(array $item): self
    {
        return new self(
            id: (int)$item['article_previous_uri_id'],
            articleId: (int)$item['article_previous_uri_article_id'],
            uri: $item['article_previous_uri_uri'],
            createdAt: DateUtil::convertStringToDateTimeImmutable(
                $item['article_previous_uri_created_at']
            ),
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'article_id' => $this->articleId,
            'uri' => $this->uri,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
        ];
    }

    public function asPublicArray(): array
    {
        return [
            'id' => $this->id,
            'uri' => $this->uri,
            'createdAt' => $this->createdAt->format('c'),
        ];
    }
}
