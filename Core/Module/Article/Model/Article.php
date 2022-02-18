<?php

namespace Amora\Core\Module\Article\Model;

use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
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
        $id = empty($article['id'])
            ? (empty($article['article_id']) ? null : $article['article_id'])
            : (int)$article['id'];

        $createdAt = empty($article['article_created_at'])
            ? $article['created_at']
            : $article['article_created_at'];

        $updatedAt = empty($article['article_updated_at'])
            ? $article['updated_at']
            : $article['article_updated_at'];

        return new self(
            id: $id,
            user: User::fromArray($article),
            status: ArticleStatus::from($article['status_id']),
            type: ArticleType::from($article['type_id']),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            publishOn: $article['published_at'],
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
            'id' => $this->getId(),
            'user' => $this->getUser()->asArray(),
            'user_id' => $this->getUser()->getId(),
            'status_id' => $this->getStatusId(),
            'type_id' => $this->getTypeId(),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt(),
            'published_at' => $this->getPublishOn(),
            'title' => $this->getTitle(),
            'content_html' => $this->getContentHtml(),
            'main_image' => $this->getMainImage() ? $this->getMainImage()->asArray() : [],
            'main_image_id' => $this->getMainImageId(),
            'uri' => $this->getUri(),
            'tags' => $this->getTags()
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getStatusId(): int
    {
        return $this->statusId;
    }

    public function getStatusName(): string
    {
        return ArticleStatus::from($this->statusId)->name;
    }

    public function getTypeId(): int
    {
        return $this->typeId;
    }

    public function getTypeName(): string
    {
        return ArticleType::from($this->type)->name;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function getPublishOn(): ?string
    {
        return $this->publishOn;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getContentHtml(): string
    {
        return $this->contentHtml;
    }

    public function getContentExcerpt(): string
    {
        return '';
    }

    public function getMainImageId(): ?int
    {
        return $this->mainImageId;
    }

    public function getMainImage(): ?Image
    {
        return $this->mainImage;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getTagsAsString(): string
    {
        $output = [];
        /** @var Tag $tag */
        foreach ($this->getTags() as $tag) {
            $output[] = $tag->getName();
        }

        return implode(', ', $output);
    }

    public function isPublished(): bool
    {
        if ($this->getStatusId() !== ArticleStatus::PUBLISHED->value) {
            return false;
        }

        if (!$this->getPublishOn()) {
            return false;
        }

        if (strtotime($this->getPublishOn()) > time()) {
            return false;
        }

        return true;
    }
}
