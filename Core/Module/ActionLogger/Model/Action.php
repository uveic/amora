<?php

namespace Amora\Core\Module\Action\Model;

class Action
{
    private ?int $id;
    private ?int $userId;
    private ?string $sessionId;
    private string $createdAt;
    private string $url;
    private ?string $referrer;
    private ?string $ip;
    private ?string $userAgent;
    private ?string $clientLanguage;

    public function __construct(
        ?int $id,
        ?int $userId,
        ?string $sessionId,
        string $createdAt,
        string $url,
        ?string $referrer = null,
        ?string $ip = null,
        ?string $userAgent = null,
        ?string $clientLanguage = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->sessionId = $sessionId;
        $this->createdAt = $createdAt;
        $this->url = $url;
        $this->referrer = $referrer;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->clientLanguage = $clientLanguage;
    }

    public function asArray(): array
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'session_id' => $this->getSessionId(),
            'created_at' => $this->getCreatedAt(),
            'url' => $this->getUrl(),
            'referrer' => $this->getReferrer(),
            'ip' => $this->getIp(),
            'user_agent' => $this->getUserAgent(),
            'client_language' => $this->getClientLanguage()
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

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getReferrer(): ?string
    {
        return $this->referrer;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getClientLanguage(): ?string
    {
        return $this->clientLanguage;
    }
}
