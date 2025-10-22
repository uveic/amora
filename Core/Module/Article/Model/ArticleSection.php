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
        public readonly ArticleSectionType $type,
        public readonly string $contentHtml,
        public readonly ?int $sequence,
        public readonly ?string $mediaCaption,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly ?Media $media = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['article_section_id'],
            articleId: $data['article_section_article_id'],
            type: ArticleSectionType::from($data['article_section_type_id']),
            contentHtml: $data['article_section_content_html'],
            sequence: $data['article_section_sequence'],
            mediaCaption: $data['article_section_media_caption'],
            createdAt: DateUtil::convertStringToDateTimeImmutable($data['article_section_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($data['article_section_updated_at']),
            media: isset($data['media_id']) ? Media::fromArray($data) : null,
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'article_id' => $this->articleId,
            'article_section_type_id' => $this->type->value,
            'content_html' => $this->contentHtml,
            'sequence' => $this->sequence,
            'media_id' => $this->media->id,
            'media_caption' => $this->mediaCaption,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
        ];
    }
}
