<?php

namespace uve\core\module\user\model;

use uve\core\value\Language;
use uve\core\module\user\value\UserRole;

class User
{
    protected ?int $id;
    protected int $languageId;
    protected int $roleId;
    protected int $journeyStatusId;
    protected string $createdAt;
    protected string $updatedAt;
    protected ?string $email;
    protected ?string $name;
    protected ?string $passwordHash;
    protected ?string $bio;
    protected bool $isEnabled;
    protected bool $verified;
    protected string $timezone;
    private ?string $previousEmailAddress;

    public function __construct(
        ?int $id,
        int $languageId,
        int $roleId,
        int $journeyStatusId,
        string $createdAt,
        string $updatedAt,
        ?string $email,
        ?string $name,
        ?string $passwordHash,
        ?string $bio,
        bool $isEnabled,
        bool $verified,
        string $timezone,
        ?string $previousEmailAddress = null
    ) {
        $this->id = $id;
        $this->languageId = $languageId;
        $this->roleId = $roleId;
        $this->journeyStatusId = $journeyStatusId;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->email = $email;
        $this->name = $name;
        $this->passwordHash = $passwordHash;
        $this->bio = $bio;
        $this->isEnabled = $isEnabled;
        $this->verified = $verified;
        $this->timezone = $timezone;
        $this->previousEmailAddress = $previousEmailAddress;
    }

    public static function fromArray(array $user): User
    {
        $id = isset($user['user_id'])
            ? (int)$user['user_id']
            : (isset($user['id']) ? (int)$user['id'] : null);

        $createdAt = $user['user_created_at'] ?? $user['created_at'];
        $updatedAt = $user['user_updated_at'] ?? $user['updated_at'];

        return new User(
            $id,
            $user['language_id'],
            $user['role_id'],
            $user['journey_id'],
            $createdAt,
            $updatedAt,
            $user['email'] ?? null,
            $user['name'] ?? null,
            $user['password_hash'] ?? null,
            $user['bio'] ?? null,
            empty($user['is_enabled']) ? false : true,
            empty($user['verified']) ? false : true,
            $user['timezone'],
            $user['previous_email_address'] ?? null
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->getId(),
            'language_id' => $this->getLanguageId(),
            'role_id' => $this->getRoleId(),
            'journey_id' => $this->getJourneyStatusId(),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt(),
            'email' => $this->getEmail(),
            'name' => $this->getName(),
            'password_hash' => $this->getPasswordHash(),
            'bio' => $this->getBio(),
            'is_enabled' => $this->isEnabled(),
            'verified' => $this->isVerified(),
            'timezone' => $this->getTimezone(),
            'previous_email_address' => $this->getPreviousEmailAddress()
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    public function getJourneyStatusId(): int
    {
        return $this->journeyStatusId;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function getPreviousEmailAddress(): ?string
    {
        return $this->previousEmailAddress;
    }

    public function getLanguageName(): string
    {
        return Language::getNameForId($this->getLanguageId());
    }

    public function getRoleName(): string
    {
        return UserRole::getNameForId($this->getRoleId());
    }

    public function getNameOrEmail(): string
    {
        if ($this->getName()) {
            return $this->getName();
        }

        return $this->getEmail();
    }

    public function getValidationHash(): string
    {
        return hash(
            'SHA512',
            $this->getId() . $this->getName() . $this->getEmail() . $this->getCreatedAt()
        );
    }

    public function validateValidationHash(string $hash): bool
    {
        return $this->getValidationHash() === $hash;
    }
}
