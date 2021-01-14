<?php

namespace uve\core\module\article\model;

use uve\core\module\user\model\User;
use uve\core\module\article\value\ArticleStatus;
use uve\core\module\article\value\ArticleType;

class Article
{
    public function __construct(
        private ?int $id,
        private User $user,
        private int $statusId,
        private int $typeId,
        private string $createdAt,
        private string $updatedAt,
        private string $title,
        private string $content,
        private ?string $mainImageSrc,
        private string $uri,
        private array $sections = []
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
            $article['title'],
            $article['content'],
            empty($article['main_image_src']) ? null : $article['main_image_src'],
            $article['uri']
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
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
            'main_image_src' => $this->getMainImageSrc(),
            'uri' => $this->getUri(),
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getContentExcerpt(): string
    {
        return '';
    }

    public function getMainImageSrc(): ?string
    {
        return $this->mainImageSrc;
    }

    public function getUri(): string
    {
        return $this->uri;
    }
}