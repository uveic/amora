<?php

namespace Amora\Core\Module\Article\Model;

class ArticleSection
{
    public function __construct(
        private ?int $id,
        private int $articleId,
        private int $articleSectionTypeId,
        private string $contentHtml,
        private ?int $order,
        private ?int $imageId,
        private ?string $imageCaption,
        private string $createdAt,
        private string $updatedAt,
    ) {}

    public static function fromArray(array $articleSection): self
    {
        $id = empty($articleSection['id'])
            ? (empty($articleSection['article_section_id']) ? null : $articleSection['article_section_id'])
            : (int)$articleSection['id'];

        $createdAt = empty($articleSection['article_section_created_at'])
            ? $articleSection['created_at']
            : $articleSection['article_section_created_at'];

        $updatedAt = empty($articleSection['article_section_updated_at'])
            ? $articleSection['updated_at']
            : $articleSection['article_section_updated_at'];

        return new self(
            $id,
            $articleSection['article_id'],
            $articleSection['article_section_type_id'],
            $articleSection['content_html'],
            $articleSection['order'],
            $articleSection['image_id'],
            $articleSection['image_caption'],
            $createdAt,
            $updatedAt,
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->getId(),
            'article_id' => $this->getArticleId(),
            'article_section_type_id' => $this->getArticleSectionTypeId(),
            'content_html' => $this->getContentHtml(),
            'order' => $this->getOrder(),
            'image_id' => $this->getImageId(),
            'image_caption' => $this->getImageCaption(),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt()
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

    public function getArticleId(): int
    {
        return $this->articleId;
    }

    public function getArticleSectionTypeId(): int
    {
        return $this->articleSectionTypeId;
    }

    public function getContentHtml(): string
    {
        return $this->contentHtml;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function getImageId(): ?int
    {
        return $this->imageId;
    }

    public function getImageCaption(): ?string
    {
        return $this->imageCaption;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }
}
