<?php

namespace Amora\Core\Module\User\Model;

use Amora\Core\Module\User\Value\VerificationType;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class UserVerification
{
    public function __construct(
        public ?int $id,
        public readonly int $userId,
        public readonly VerificationType $type,
        public readonly ?string $email,
        public readonly DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $verifiedAt,
        public readonly string $verificationIdentifier,
        public readonly bool $isEnabled
    ) {}

    public static function fromArray(array $item): self
    {
        return new self(
            (int)$item['user_verification_id'],
            $item['user_id'],
            VerificationType::from($item['type_id']),
            $item['email'] ?? null,
            DateUtil::convertStringToDateTimeImmutable($item['created_at']),
            isset($item['verified_at'])
                ? DateUtil::convertStringToDateTimeImmutable($item['verified_at'])
                : null,
            $item['verification_identifier'],
            !empty($item['is_enabled']),
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'type_id' => $this->type->value,
            'email' => $this->email,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'verified_at' => $this->verifiedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'verification_identifier' => $this->verificationIdentifier,
            'is_enabled' => $this->isEnabled,
        ];
    }
}
