<?php

namespace Amora\Core\Module\User\Model;

use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;
use DateTimeZone;

class Session
{
    public function __construct(
        public ?int $id,
        public readonly string $sessionId,
        public readonly User $user,
        public readonly DateTimeZone $timezone,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $lastVisitedAt,
        public readonly DateTimeImmutable $validUntil,
        public readonly ?DateTimeImmutable $forcedExpirationAt = null,
        public readonly ?string $ip = null,
        public readonly ?string $browserAndPlatform = null
    ) {}

    public static function fromArray(array $session): Session
    {
        return new Session(
            id: (int)$session['session_id'],
            sessionId: $session['session_sid'],
            user: User::fromArray($session),
            timezone: DateUtil::convertStringToDateTimeZone($session['session_timezone']),
            createdAt: DateUtil::convertStringToDateTimeImmutable($session['session_created_at']),
            lastVisitedAt: DateUtil::convertStringToDateTimeImmutable($session['session_last_visited_at']),
            validUntil: DateUtil::convertStringToDateTimeImmutable($session['session_valid_until']),
            forcedExpirationAt: isset($session['session_forced_expiration_at'])
                ? DateUtil::convertStringToDateTimeImmutable($session['session_forced_expiration_at'])
                : null,
            ip: $session['session_ip'],
            browserAndPlatform: $session['session_browser_and_platform'],
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user->id,
            'sid' => $this->sessionId,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'last_visited_at' => $this->lastVisitedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'valid_until' => $this->validUntil->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'forced_expiration_at' => $this->forcedExpirationAt?->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'timezone' => $this->timezone->getName(),
            'ip' => $this->ip,
            'browser_and_platform' => $this->browserAndPlatform,
        ];
    }

    public function isAuthenticated(): bool
    {
        if (!empty($this->forcedExpirationAt)) {
            return false;
        }

        if (!$this->user->isEnabled()) {
            return false;
        }

        $twoHoursFromNow = time() + 60 * 60 * 2;
        return $this->validUntil->getTimestamp() > $twoHoursFromNow;
    }

    public function isExpired(): bool
    {
        if ($this->forcedExpirationAt) {
            return true;
        }

        if ($this->validUntil < new DateTimeImmutable()) {
            return true;
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->isAuthenticated() && $this->user->role === UserRole::Admin;
    }
}
