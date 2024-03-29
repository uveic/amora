<?php

namespace Amora\Core\Module\User\Model;

use Amora\Core\Util\DateUtil;
use Amora\App\Value\Language;
use DateTimeImmutable;

class UserRegistrationRequest
{
    public function __construct(
        public ?int $id,
        public readonly string $email,
        public readonly Language $language,
        public readonly DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $processedAt,
        public readonly string $requestCode,
        public readonly ?int $userId,
    ) {}

    public static function fromArray(array $item): self
    {
        return new self(
            id: (int)$item['user_registration_request_id'],
            email: $item['email'],
            language: Language::from($item['language_iso_code']),
            createdAt: DateUtil::convertStringToDateTimeImmutable($item['created_at']),
            processedAt: isset($item['processed_at'])
                ? DateUtil::convertStringToDateTimeImmutable($item['processed_at'])
                : null,
            requestCode: $item['request_code'],
            userId: isset($item['user_id']) ? (int)$item['user_id'] : null,
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'language_iso_code' => $this->language->value,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'processed_at' => $this->processedAt?->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'request_code' => $this->requestCode,
            'user_id' => $this->userId,
        ];
    }
}
