<?php

namespace Amora\Core\Module\Article\Model;

use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;

class Article
{
    public function __construct(
        private ?int $id,
        private User $user,
        private int $statusId,
        private int $typeId,
        private string $createdAt,
        private string $updatedAt,
        private ?string $publishOn,
        private ?string $title,
        private string $contentHtml,
        private ?int $mainImageId,
        private ?Image $mainImage,
        private string $uri,
        private array $sections = [],
        private array $tags = [],
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
            $id,
            User::fromArray($article),
            $article['status_id'],
            $article['type_id'],
            $createdAt,
            $updatedAt,
            $article['published_at'],
            $article['title'] ?? null,
            $article['content_html'],
            empty($article['main_image_id']) ? null : (int)$article['main_image_id'],
            empty($article['main_image_id']) ? null : Image::fromArray($article),
            $article['uri'],
            $article['sections'] ?? [],
            $article['tags'] ?? [],
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
        return ArticleStatus::getNameForId($this->getStatusId());
    }

    public function getTypeId(): int
    {
        return $this->typeId;
    }

    public function getTypeName(): string
    {
        return ArticleType::getNameForId($this->getTypeId());
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
        if ($this->getStatusId() !== ArticleStatus::PUBLISHED) {
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
