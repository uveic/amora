<?php

namespace Amora\Core\Module\Article\Model;

use Amora\Core\Core;
use Amora\Core\Module\Article\Entity\RawFile;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\UrlBuilderUtil;
use DateTimeImmutable;

class Media
{
    public function __construct(
        public ?int $id,
        public readonly MediaType $type,
        public readonly MediaStatus $status,
        public readonly ?User $user,
        public readonly ?string $path,
        public readonly string $filenameOriginal,
        public readonly ?string $filenameLarge,
        public readonly ?string $filenameMedium,
        public readonly ?string $filenameSmall,
        public readonly ?string $captionHtml,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['media_id'],
            type: MediaType::from($data['media_type_id']),
            status: MediaStatus::from($data['media_status_id']),
            user: isset($data['user_id']) ? User::fromArray($data) : null,
            path: $data['media_path'] ?? null,
            filenameOriginal: $data['media_filename_original'],
            filenameLarge: $data['media_filename_large'] ?? null,
            filenameMedium: $data['media_filename_medium'] ?? null,
            filenameSmall: $data['media_filename_small'] ?? null,
            captionHtml: $data['media_caption_html'] ?? null,
            createdAt: DateUtil::convertStringToDateTimeImmutable($data['media_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($data['media_updated_at']),
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'type_id' => $this->type->value,
            'status_id' => $this->status->value,
            'user_id' => $this->user?->id,
            'user' => $this->user ? $this->user->asArray() : [],
            'path' => $this->path,
            'filename_original' => $this->filenameOriginal,
            'filename_small' => $this->filenameSmall,
            'filename_medium' => $this->filenameMedium,
            'filename_large' => $this->filenameLarge,
            'caption' => $this->captionHtml,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
        ];
    }

    public function buildPublicDataArray(): array
    {
        return [
            'id' => $this->id,
            'path' => $this->getPathWithNameMedium(),
            'fullPath' => UrlBuilderUtil::buildBaseUrlWithoutLanguage() . $this->getPathWithNameMedium(),
            'caption' => $this->captionHtml,
            'captionHtml' => $this->captionHtml,
            'name' => $this->type === MediaType::Image
                ? $this->filenameMedium
                : $this->filenameOriginal,
            'createdAt' => $this->createdAt->format('c'),
            'userId' => $this->user?->id,
            'userName' => $this->user?->getNameOrEmail(),
        ];
    }

    public function getDirWithNameOriginal(): string
    {
        return $this->buildDirPath() . $this->filenameOriginal;
    }

    public function getDirWithNameSmall(): string
    {
        return $this->filenameSmall
            ? $this->buildDirPath() . $this->filenameSmall
            : $this->getDirWithNameMedium();
    }

    public function getDirWithNameMedium(): string
    {
        return $this->filenameMedium
            ? $this->buildDirPath() . $this->filenameMedium
            : $this->getDirWithNameOriginal();
    }

    public function getDirWithNameLarge(): string
    {
        return $this->filenameLarge
            ? $this->buildDirPath() . $this->filenameLarge
            : $this->getDirWithNameMedium();
    }

    public function getPathWithNameOriginal(): string
    {
        return $this->buildPath() . $this->filenameOriginal;
    }

    public function getPathWithNameSmall(): string
    {
        if ($this->type !== MediaType::Image) {
            return $this->getPathWithNameOriginal();
        }

        return $this->filenameSmall
            ? $this->buildPath() . $this->filenameSmall
            : $this->getPathWithNameMedium();
    }

    public function getPathWithNameMedium(): string
    {
        if ($this->type !== MediaType::Image) {
            return $this->getPathWithNameOriginal();
        }

        return $this->filenameMedium
            ? $this->buildPath() . $this->filenameMedium
            : $this->getPathWithNameOriginal();
    }

    public function getPathWithNameLarge(): string
    {
        if ($this->type !== MediaType::Image) {
            return $this->getPathWithNameOriginal();
        }

        return $this->filenameLarge
            ? $this->buildPath() . $this->filenameLarge
            : $this->getPathWithNameMedium();
    }

    public function asRawFile(string $extension): RawFile
    {
        return new RawFile(
            originalName: $this->filenameOriginal,
            name: $this->filenameOriginal,
            basePath: rtrim(Core::getConfig()->mediaBaseDir, ' /'),
            extraPath: $this->path ?? '',
            extension: $extension,
            mediaType: MediaType::Image,
        );
    }

    private function buildDirPath(): string
    {
        $partialPath = $this->path
            ? (trim($this->path, '/ ') . '/')
            : '';
        return Core::getConfig()->mediaBaseDir . '/' . $partialPath;
    }

    private function buildPath(): string
    {
        $partialPath = $this->path
            ? (trim($this->path, '/ ') . '/')
            : '';
        return Core::getConfig()->mediaBaseUrl . '/' . $partialPath;
    }
}
