<?php

namespace uve\core\module\user\model;

class UserRegistrationRequest
{
    public function __construct(
        private ?int $id,
        private string $email,
        private int $languageId,
        private string $createdAt,
        private ?string $processedAt,
        private string $requestCode,
        private ?int $userId,
    ) {}

    public static function fromArray(array $item): self
    {
        $id = empty($item['user_registration_request_id'])
            ? (empty($item['id']) ? null : (int)$item['id'])
            : (int)$item['user_registration_request_id'];

        return new self(
            $id,
            $item['email'],
            (int)$item['language_id'],
            $item['created_at'],
            $item['processed_at'] ?? null,
            $item['request_code'],
            $item['user_id'] ?? null,
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'language_id' => $this->getLanguageId(),
            'created_at' => $this->getCreatedAt(),
            'processed_at' => $this->getProcessedAt(),
            'request_code' => $this->getRequestCode(),
            'user_id' => $this->getUserId(),
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getProcessedAt(): ?string
    {
        return $this->processedAt;
    }

    public function getRequestCode(): string
    {
        return $this->requestCode;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }
}
