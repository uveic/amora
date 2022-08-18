<?php

namespace Amora\Core\Module\Article\Model;

use Amora\Core\Core;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\DateUtil;
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
        public readonly ?string $caption,
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
            caption: $data['media_caption'] ?? null,
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
            'filename_medium' => $this->filenameMedium,
            'filename_large' => $this->filenameLarge,
            'caption' => $this->caption,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
        ];
    }

    public function buildPublicDataArray(): array
    {
        return [
            'id' => $this->id,
            'uri' => $this->getUriWithNameMedium(),
            'caption' => $this->caption,
            'name' => $this->filenameMedium,
            'createdAt' => $this->createdAt->format('c'),
            'userId' => $this->user?->id,
            'userName' => $this->user?->getNameOrEmail(),
        ];
    }

    public function getPathWithNameOriginal(): string
    {
        $path = Core::getConfig()->mediaBaseDir . ($this->path ? trim($this->path, '/ ') . '/' : '/');
        return $path . $this->filenameOriginal;
    }

    public function getPathWithNameMedium(): ?string
    {
        $path = Core::getConfig()->mediaBaseDir . ($this->path ? trim($this->path, '/ ') . '/' : '/');
        return $this->filenameMedium ? ($path . $this->filenameMedium) : null;
    }

    public function getPathWithNameLarge(): ?string
    {
        $path = Core::getConfig()->mediaBaseDir . ($this->path ? trim($this->path, '/ ') . '/' : '/');
        return $this->filenameLarge ? ($path . $this->filenameLarge) : null;
    }

    public function getUriWithNameOriginal(): string
    {
        $path = Core::getConfig()->mediaBaseUrl . ($this->path ? trim($this->path, '/ ') . '/' : '/');
        return $path . $this->filenameOriginal;
    }

    public function getUriWithNameMedium(): ?string
    {
        $path = Core::getConfig()->mediaBaseUrl . ($this->path ? trim($this->path, '/ ') . '/' : '/');
        return $this->filenameMedium ? ($path . $this->filenameMedium) : null;
    }

    public function getUriWithNameLarge(): ?string
    {
        $path = Core::getConfig()->mediaBaseUrl . ($this->path ? trim($this->path, '/ ') . '/' : '/');
        return $this->filenameLarge ? ($path . $this->filenameLarge) : null;
    }
}
