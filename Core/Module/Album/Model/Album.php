<?php

namespace Amora\Core\Module\Album\Model;

use Amora\Core\Core;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Module\Album\Value\Template;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\DateUtil;
use Amora\App\Value\Language;
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
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly ?string $titleHtml,
        public readonly ?string $contentHtml,
        public readonly string $path,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['album_id'],
            language: Language::from($data['album_language_iso_code']),
            user: User::fromArray($data),
            status: AlbumStatus::from($data['album_status_id']),
            mainMedia: Media::fromArray($data),
            template: Template::from($data['album_type_id']),
            createdAt: DateUtil::convertStringToDateTimeImmutable($data['album_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($data['album_updated_at']),
            titleHtml: $data['album_title'] ?? null,
            contentHtml: $data['album_content_html'],
            path: $data['album_path'],
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
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'title_html' => $this->titleHtml,
            'content_html' => $this->contentHtml,
            'path' => $this->path,
        ];
    }

    private function getFullPath(): string
    {
        return rtrim(Core::getConfig()->baseUrl, '/ ') . '/' . $this->path;
    }
}
