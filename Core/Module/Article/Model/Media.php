<?php

namespace Amora\Core\Module\Article\Model;

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
            caption: $data['caption'] ?? null,
            createdAt: DateUtil::convertStringToDateTimeImmutable($data['media_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($data['media_updated_at']),
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'media_type_id' => $this->type->value,
            'media_status_id' => $this->status->value,
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
            'uri' => $this->getPathWithName(),
            'caption' => $this->caption,
        ];
    }

    public function getPathWithName(): string
    {
        return rtrim($this->path, '/ ') . '/'
            . ($this->filenameMedium ?? ($this->filenameLarge ?? $this->filenameOriginal));
    }

    public function getExtension(): string
    {
        if (!str_contains($this->filenameOriginal, '.')) {
            return '';
        }

        $parts = explode('.', $this->filenameOriginal);
        return strtolower(trim($parts[count($parts) - 1]));
    }
}
