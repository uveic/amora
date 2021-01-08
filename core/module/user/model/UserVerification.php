<?php

namespace uve\core\module\user\model;

class UserVerification
{
    private ?int $id;
    private int $userId;
    private int $typeId;
    private string $createdAt;
    private ?string $verifiedAt;
    private string $verificationIdentifier;
    private bool $isEnabled;

    public function __construct(
        ?int $id,
        int $userId,
        int $typeId,
        string $createdAt,
        ?string $verifiedAt,
        string $verificationIdentifier,
        bool $isEnabled
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->typeId = $typeId;
        $this->createdAt = $createdAt;
        $this->verifiedAt = $verifiedAt;
        $this->verificationIdentifier = $verificationIdentifier;
        $this->isEnabled = $isEnabled;
    }

    public static function fromArray(array $item): self
    {
        $id = empty($item['id'])
            ? (empty($item['user_verification_id']) ? null : $item['user_verification_id'])
            : (int)$item['id'];

        return new self(
            $id,
            $item['user_id'],
            $item['type_id'],
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
