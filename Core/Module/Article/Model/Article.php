<?php

namespace Amora\Core\Module\Article\Model;

use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class Article
{
    public function __construct(
        public ?int $id,
        public readonly User $user,
        public readonly ArticleStatus $status,
        public readonly ArticleType $type,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly ?DateTimeImmutable $publishOn,
        public readonly ?string $title,
        public readonly string $contentHtml,
        public readonly ?int $mainImageId,
        public readonly ?Image $mainImage,
        public readonly string $uri,
        public readonly array $tags = [],
    ) {}

    public static function fromArray(array $article): self
    {
        return new self(
            id: (int)$article['article_id'],
            user: User::fromArray($article),
            status: ArticleStatus::from($article['status_id']),
            type: ArticleType::from($article['type_id']),
            createdAt: DateUtil::convertStringToDateTimeImmutable($article['article_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($article['article_updated_at']),
            publishOn: isset($article['published_at'])
                ? DateUtil::convertStringToDateTimeImmutable($article['published_at'])
                : null,
            title: $article['title'] ?? null,
            contentHtml: $article['content_html'],
            mainImageId: empty($article['main_image_id']) ? null : (int)$article['main_image_id'],
            mainImage: empty($article['main_image_id']) ? null : Image::fromArray($article),
            uri: $article['uri'],
            tags: $article['tags'] ?? [],
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user->id,
            'status_id' => $this->status->value,
            'type_id' => $this->type->value,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'published_at' => $this->publishOn?->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'title' => $this->title,
            'content_html' => $this->contentHtml,
            'main_image_id' => $this->mainImageId,
            'uri' => $this->uri,
            'tags' => $this->tags,
        ];
    }

    public function getContentExcerpt(): string
    {
        $content = strip_tags($this->contentHtml);
        return trim(
            substr(
                string: $content,
                offset: 0,
                length: 250,
            )
        );
    }

    public function getTagsAsString(): string
    {
        $output = [];
        /** @var Tag $tag */
        foreach ($this->tags as $tag) {
            $output[] = $tag->name;
        }

        return implode(', ', $output);
    }

    public function isPublished(): bool
    {
        if ($this->status !== ArticleStatus::Published) {
            return false;
        }

        if (!$this->publishOn) {
            return false;
        }

        if ($this->publishOn->getTimestamp() > time()) {
            return false;
        }

        return true;
    }
}
