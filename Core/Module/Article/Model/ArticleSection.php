<?php

namespace Amora\Core\Module\Article\Model;

use Amora\Core\Module\Article\Value\ArticleSectionType;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class ArticleSection
{
    public function __construct(
        public ?int $id,
        public readonly int $articleId,
        public readonly ArticleSectionType $articleSectionType,
        public readonly string $contentHtml,
        public readonly ?int $sequence,
        public readonly ?int $mediaId,
        public readonly ?string $mediaCaption,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
    ) {}

    public static function fromArray(array $articleSection): self
    {
        $id = empty($articleSection['id'])
            ? (empty($articleSection['article_section_id']) ? null : $articleSection['article_section_id'])
            : (int)$articleSection['id'];

        $createdAt = empty($articleSection['article_section_created_at'])
            ? DateUtil::convertStringToDateTimeImmutable($articleSection['created_at'])
            : DateUtil::convertStringToDateTimeImmutable($articleSection['article_section_created_at']);

        $updatedAt = empty($articleSection['article_section_updated_at'])
            ? DateUtil::convertStringToDateTimeImmutable($articleSection['updated_at'])
            : DateUtil::convertStringToDateTimeImmutable($articleSection['article_section_updated_at']);

        return new self(
            id: $id,
            articleId: $articleSection['article_id'],
            articleSectionType: ArticleSectionType::from($articleSection['article_section_type_id']),
            contentHtml: $articleSection['content_html'],
            sequence: $articleSection['sequence'],
            mediaId: $articleSection['media_id'],
            mediaCaption: $articleSection['media_caption'],
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'article_id' => $this->articleId,
            'article_section_type_id' => $this->articleSectionType->value,
            'content_html' => $this->contentHtml,
            'sequence' => $this->sequence,
            'media_id' => $this->mediaId,
            'media_caption' => $this->mediaCaption,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
        ];
    }
}
