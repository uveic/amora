<?php

namespace Amora\Core\Module\User\Model;

use Amora\App\Value\AppUserRole;
use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Module\User\Value\UserStatus;
use Amora\Core\Util\DateUtil;
use Amora\Core\Module\User\Value\UserRole;
use Amora\App\Value\Language;
use DateTimeImmutable;
use DateTimeZone;

class User
{
    public function __construct(
        public ?int $id,
        public readonly UserStatus $status,
        public readonly Language $language,
        public readonly UserRole|AppUserRole $role,
        public readonly UserJourneyStatus $journeyStatus,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly ?string $email,
        public readonly ?string $name,
        public readonly ?string $passwordHash,
        public readonly ?string $bio,
        public readonly ?string $identifier,
        public readonly DateTimeZone $timezone,
        public readonly ?string $changeEmailAddressTo = null,
    ) {}

    public static function fromArray(array $user): User
    {
        $userRole = UserRole::tryFrom($user['user_role_id'])
            ? UserRole::from($user['user_role_id'])
            : AppUserRole::from($user['user_role_id']);

        return new User(
            id: (int)$user['user_id'],
            status: UserStatus::from($user['user_status_id']),
            language: Language::from($user['user_language_iso_code']),
            role: $userRole,
            journeyStatus: UserJourneyStatus::from($user['user_journey_id']),
            createdAt: DateUtil::convertStringToDateTimeImmutable($user['user_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($user['user_updated_at']),
            email: $user['user_email'] ?? null,
            name: $user['user_name'] ?? ($user['name'] ?? null),
            passwordHash: $user['user_password_hash'] ?? null,
            bio: $user['user_bio'] ?? null,
            identifier: $user['user_identifier'] ?? null,
            timezone: DateUtil::convertStringToDateTimeZone($user['user_timezone']),
            changeEmailAddressTo: $user['user_change_email_to'] ?? null,
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'status_id' => $this->status->value,
            'language_iso_code' => $this->language->value,
            'role_id' => $this->role->value,
            'journey_id' => $this->journeyStatus->value,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'email' => $this->email,
            'name' => $this->name,
            'password_hash' => $this->passwordHash,
            'bio' => $this->bio,
            'identifier' => $this->identifier,
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

    public function isVerified(): bool
    {
        return $this->journeyStatus === UserJourneyStatus::Registration;
    }

    public function isEnabled(): bool
    {
        return $this->status === UserStatus::Enabled;
    }
}
