<?php

namespace uve\core\module\user\model;

use uve\core\module\user\value\UserRole;

class Session
{
    private ?int $id;
    private string $sessionId;
    private User $user;
    private string $createdAt;
    private string $lastVisitedAt;
    private string $validUntil;
    private ?string $forcedExpirationAt;
    private string $timezone;
    private ?string $ip;
    private ?string $browserAndPlatform;

    public function __construct(
        ?int $id,
        string $sessionId,
        User $user,
        string $createdAt,
        string $lastVisitedAt,
        string $validUntil,
        ?string $forcedExpirationAt = null,
        string $timezone = 'UTC',
        ?string $ip = null,
        ?string $browserAndPlatform = null
    ) {
        $this->id = $id;
        $this->sessionId = $sessionId;
        $this->user = $user;
        $this->createdAt = $createdAt;
        $this->lastVisitedAt = $lastVisitedAt;
        $this->forcedExpirationAt = $forcedExpirationAt;
        $this->validUntil = $validUntil;
        $this->timezone = $timezone;
        $this->ip = $ip;
        $this->browserAndPlatform = $browserAndPlatform;
    }

    public static function fromArray(array $session, User $user): Session
    {
        $id = isset($session['session_id'])
            ? (int)$session['session_id']
            : (empty($session['id']) ? null : (int)$session['id']);
        $createdAt = $session['session_created_at'] ?? $session['created_at'];

        return new Session(
            $id,
            $session['sid'],
            $user,
            $createdAt,
            $session['last_visited_at'],
            $session['valid_until'],
            $session['forced_expiration_at'],
            $session['timezone'],
            $session['ip'],
            $session['browser_and_platform']
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUser()->getId(),
            'sid' => $this->getSessionId(),
            'created_at' => $this->getCreatedAt(),
            'last_visited_at' => $this->getLastVisitedAt(),
            'valid_until' => $this->getValidUntil(),
            'forced_expiration_at' => $this->getForcedExpirationAt(),
            'user' => $this->getUser()->asArray(),
            'timezone' => $this->getTimezone(),
            'ip' => $this->getIp(),
            'browser_and_platform' => $this->getBrowserAndPlatform()
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getLastVisitedAt(): string
    {
        return $this->lastVisitedAt;
    }

    public function getValidUntil(): string
    {
        return $this->validUntil;
    }

    public function getForcedExpirationAt(): ?string
    {
        return $this->forcedExpirationAt;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getBrowserAndPlatform(): ?string
    {
        return $this->browserAndPlatform;
    }

    public function getValidUntilTimestamp(): int
    {
        return strtotime($this->getValidUntil());
    }

    public function isAuthenticated(): bool
    {
        if (!empty($this->getForcedExpirationAt())) {
            return false;
        }

        $twoHoursFromNow = time() + 60 * 60 * 2;
        return $this->getValidUntilTimestamp() > $twoHoursFromNow;
    }

    public function isAdmin(): bool
    {
        return $this->getUser()->getRoleId() === UserRole::ADMIN;
    }
}
