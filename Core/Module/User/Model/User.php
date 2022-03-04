<?php

namespace Amora\Core\Module\User\Model;

use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Util\DateUtil;
use Amora\Core\Module\User\Value\UserRole;
use Amora\App\Value\Language;
use DateTimeImmutable;
use DateTimeZone;

class User
{
    public function __construct(
        public ?int $id,
        public readonly Language $language,
        public readonly UserRole $role,
        public readonly UserJourneyStatus $journeyStatus,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly ?string $email,
        public readonly ?string $name,
        public readonly ?string $passwordHash,
        public readonly ?string $bio,
        public readonly bool $isEnabled,
        public readonly bool $verified,
        public readonly DateTimeZone $timezone,
        public readonly ?string $changeEmailAddressTo = null,
    ) {}

    public static function fromArray(array $user): User
    {
        return new User(
            id: (int)$user['user_id'],
            language: Language::from($user['user_language_iso_code']),
            role: UserRole::from($user['role_id']),
            journeyStatus: UserJourneyStatus::from($user['journey_id']),
            createdAt: DateUtil::convertStringToDateTimeImmutable($user['user_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($user['user_updated_at']),
            email: $user['email'] ?? null,
            name: $user['user_name'] ?? ($user['name'] ?? null),
            passwordHash: $user['password_hash'] ?? null,
            bio: $user['bio'] ?? null,
            isEnabled: !empty($user['is_enabled']),
            verified: !empty($user['verified']),
            timezone: DateUtil::convertStringToDateTimeZone($user['timezone']),
            changeEmailAddressTo: $user['change_email_to'] ?? null,
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'language_iso_code' => $this->language->value,
            'role_id' => $this->role->value,
            'journey_id' => $this->journeyStatus->value,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'email' => $this->email,
            'name' => $this->name,
            'password_hash' => $this->passwordHash,
            'bio' => $this->bio,
            'is_enabled' => $this->isEnabled,
            'verified' => $this->verified,
            'timezone' => $this->timezone->getName(),
            'change_email_to' => $this->changeEmailAddressTo,
        ];
    }

    public function getNameOrEmail(): string
    {
        if ($this->name) {
            return $this->name;
        }

        return $this->email;
    }

    public function getValidationHash(): string
    {
        return hash(
            'SHA512',
            $this->id . $this->name . $this->email . $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT)
        );
    }

    public function validateValidationHash(string $hash): bool
    {
        return $this->getValidationHash() === $hash;
    }
}
