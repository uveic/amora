<?php

namespace Amora\Core\Module\Album\Model;

use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Module\Album\Value\Template;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\DateUtil;
use Amora\App\Value\Language;
use Amora\Core\Util\UrlBuilderUtil;
use DateTimeImmutable;

class Album
{
    public function __construct(
        public ?int $id,
        public readonly Language $language,
        public readonly User $user,
        public readonly AlbumStatus $status,
        public readonly Media $mainMedia,
        public readonly Template $template,
        public readonly AlbumSlug $slug,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly ?string $titleHtml,
        public readonly ?string $contentHtml,
        public readonly array $sections = [],
    ) {}

    public static function fromArray(array $data, array $sections = []): self
    {
        return new self(
            id: isset($data['album_id']) ? (int)$data['album_id'] : null,
            language: Language::from($data['album_language_iso_code']),
            user: User::fromArray($data),
            status: AlbumStatus::from($data['album_status_id']),
            mainMedia: Media::fromArray($data),
            template: Template::from($data['album_template_id']),
            slug: AlbumSlug::fromArray($data),
            createdAt: DateUtil::convertStringToDateTimeImmutable($data['album_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($data['album_updated_at']),
            titleHtml: $data['album_title_html'] ?? null,
            contentHtml: $data['album_content_html'],
            sections: $sections,
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'language_iso_code' => $this->language->value,
            'user_id' => $this->user->id,
            'status_id' => $this->status->value,
            'main_media_id' => $this->mainMedia->id,
            'template_id' => $this->template->value,
            'slug_id' => $this->slug->id,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'title_html' => $this->titleHtml,
            'content_html' => $this->contentHtml,
        ];
    }

    private function getFullPath(): string
    {
        return UrlBuilderUtil::buildPublicAlbumUrl(
            slug: $this->slug->slug,
            language: $this->language,
        );
    }

    public function buildDescription(): string
    {
        return $this->titleHtml;
    }

    public function buildPublicDataArray(): array
    {
        return [
            'id' => $this->id,
            'languageIsoCode' => $this->language->value,
            'userId' => $this->user->id,
            'userName' => $this->user->getNameOrEmail(),
            'path' => $this->getFullPath(),
            'title' => $this->titleHtml,
            'publishedOn' => $this->createdAt->format('c'),
            'tags' => [],
        ];
    }
}
