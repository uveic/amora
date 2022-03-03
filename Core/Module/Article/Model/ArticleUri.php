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
            id: (int)$item['article_uri_id'],
            articleId: (int)$item['article_id'],
            uri: $item['uri'],
            createdAt: DateUtil::convertStringToDateTimeImmutable($item['created_at']),
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
}
