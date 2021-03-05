<?php

namespace Amora\Core\Module\User\Model;

class UserVerification
{
    public function __construct(
        private ?int $id,
        private int $userId,
        private int $typeId,
        private ?string $email,
        private string $createdAt,
        private ?string $verifiedAt,
        private string $verificationIdentifier,
        private bool $isEnabled
    ) {}

    public static function fromArray(array $item): self
    {
        $id = empty($item['user_verification_id'])
            ? (empty($item['id']) ? null : (int)$item['id'])
            : (int)$item['user_verification_id'];

        return new self(
            $id,
            $item['user_id'],
            $item['type_id'],
            $item['email'] ?? null,
            $item['created_at'],
            $item['verified_at'] ?? null,
            $item['verification_identifier'],
            empty($item['is_enabled']) ? false : true
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'type_id' => $this->getTypeId(),
            'email' => $this->getEmail(),
            'created_at' => $this->getCreatedAt(),
            'verified_at' => $this->getVerifiedAt(),
            'verification_identifier' => $this->getVerificationIdentifier(),
            'is_enabled' => $this->isEnabled()
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

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getTypeId(): int
    {
        return $this->typeId;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getVerifiedAt(): ?string
    {
        return $this->verifiedAt;
    }

    public function getVerificationIdentifier(): string
    {
        return $this->verificationIdentifier;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
