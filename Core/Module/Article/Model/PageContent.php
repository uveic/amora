<?php

namespace Amora\App\Module\Form\Entity;

use Amora\App\Value\AppPageContentType;
use Amora\App\Value\Language;
use Amora\Core\Module\Album\Model\Collection;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\PageContentType;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class PageContent
{
    public function __construct(
        public ?int $id,
        public readonly User $user,
        public readonly Language $language,
        public readonly AppPageContentType|PageContentType $type,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly ?string $title,
        public readonly ?string $subtitle,
        public readonly ?string $contentHtml,
        public readonly ?Media $mainImage,
        public readonly ?string $actionUrl,
        public readonly ?Collection $collection,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $type = PageContentType::tryFrom($data['page_content_type_id'])
            ? PageContentType::from($data['page_content_type_id'])
            : AppPageContentType::from($data['page_content_type_id']);

        return new self(
            id: (int)$data['page_content_id'],
            user: User::fromArray($data),
            language: Language::from($data['page_content_language_iso_code']),
            type: $type,
            createdAt: DateUtil::convertStringToDateTimeImmutable($data['page_content_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($data['page_content_updated_at']),
            title: $data['page_content_title'] ?? null,
            subtitle: $data['page_content_subtitle'] ?? null,
            contentHtml: $data['page_content_html'] ?? null,
            mainImage: isset($data['media_id']) ? Media::fromArray($data) : null,
            actionUrl: $data['page_content_action_url'] ?? null,
            collection: isset($data['collection_id']) ? Collection::fromArray($data) : null,
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user->id,
            'language_iso_code' => $this->language->value,
            'type_id' => $this->type->value,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'content_html' => $this->contentHtml,
            'main_image_id' => $this->mainImage?->id,
            'action_url' => $this->actionUrl,
            'collection_id' => $this->collection?->id,
        ];
    }

    public static function getEmpty(
        User $user,
        Language $language,
        AppPageContentType|PageContentType $type,
        ?Collection $collection,
    ): self {
        return new self(
            id: null,
            user: $user,
            language: $language,
            type: $type,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
            title: null,
            subtitle: null,
            contentHtml: null,
            mainImage: null,
            actionUrl: null,
            collection: $collection,
        );
    }
}
