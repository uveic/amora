<?php

namespace Amora\Core\Module\User\Service;

use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;
use DateTimeZone;
use UserAgentParserUtil;
use Amora\Core\Core;
use Amora\Core\Module\User\DataLayer\SessionDataLayer;
use Amora\Core\Module\User\Model\Session;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\StringUtil;

class SessionService
{
    private ?Session $session = null;

    public function __construct(
        private readonly string $sessionIdCookieName,
        private readonly int $sessionIdCookieValidForSeconds,
        private readonly SessionDataLayer $dataLayer,
    ) {
    }

    public function filterSessionBy(
        array $sessionIds = [],
        array $userIds = [],
        ?bool $isExpired = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->dataLayer->filterSessionBy(
            sessionIds: $sessionIds,
            userIds: $userIds,
            isExpired: $isExpired,
            queryOptions: $queryOptions,
        );
    }

    public function getSessionForSessionId(string $sessionId): ?Session
    {
        $res = $this->filterSessionBy(
            sessionIds: [$sessionId],
        );

        return $res[0] ?? null;
    }

    public function generateSessionId(): string
    {
        return  date('YmdHis') . StringUtil::generateRandomString(16);
    }

    private function generateNewValidUntil(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat(
            format: 'U',
            datetime: time() + $this->sessionIdCookieValidForSeconds,
        );
    }

    public function login(
        User $user,
        DateTimeZone $timezone,
        ?string $ip = null,
        ?string $userAgent = null,
    ): ?Session {
        $now = new DateTimeImmutable();
        $newValidUntil = $this->generateNewValidUntil();

        $session = new Session(
            id: null,
            sessionId: $this->generateSessionId(),
            user: $user,
            timezone: $timezone,
            createdAt: $now,
            lastVisitedAt: $now,
            validUntil: $newValidUntil,
            forcedExpirationAt: null,
            ip: $ip,
            browserAndPlatform: UserAgentParserUtil::parse($userAgent)->getBrowserAndPlatform(),
        );

        $this->updateBrowserCookie($session->sessionId, $newValidUntil);
        return $this->dataLayer->createNewSession($session);
    }

    public function logout(Session $session): bool
    {
        $this->updateBrowserCookie(
            sid: $session->sessionId,
            newExpiryDate: DateUtil::convertUnixTimestampToDateTimeImmutable(time() - 60 * 60 * 2),
        );
        return $this->dataLayer->expireSession($session->id);
    }

    public function expireAllSessionsForUser(int $userId): bool
    {
        return $this->dataLayer->expireAllSessionsForUser($userId);
    }

    public function updateBrowserCookie(string $sid, ?DateTimeImmutable $newExpiryDate = null): void
    {
        $newExpiryDate = $newExpiryDate ?? $this->generateNewValidUntil();

        $isLive = Core::isRunningInLiveEnv();
        $options = [
            'expires' => $newExpiryDate->getTimestamp(),
            'path' => '/',
            'secure' => $isLive,
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        if ($isLive) {
            $options['domain'] = parse_url(Core::getConfig()->baseUrl, PHP_URL_HOST);
        }

        setcookie($this->sessionIdCookieName, $sid, $options);
        $_COOKIE[$this->sessionIdCookieName] = $sid;
    }

    public function updateSessionExpiryDateAndValidUntil(string $sid, int $sessionId): bool
    {
        $newExpiryDate = $this->generateNewValidUntil();
        $this->updateBrowserCookie($sid, $newExpiryDate);
        return $this->dataLayer->updateSessionExpiryDateAndValidUntil(
            sessionId: $sessionId,
            newExpiryDate: $newExpiryDate,
        );
    }

    public function loadSession(?string $sessionId, bool $forceReload = false): ?Session
    {
        if (empty($sessionId)) {
            return null;
        }

        if (!$forceReload && !empty($this->session)) {
            return $this->session;
        }

        $this->session = $this->getSessionForSessionId($sessionId);
        if ($this->session) {
            Core::updateTimezone($this->session->timezone->getName());
        }

        return $this->session;
    }

    public function updateTimezoneForUserId(int $userId, DateTimeZone $newTimezone): bool
    {
        return $this->dataLayer->updateTimezoneForUserId($userId, $newTimezone);
    }
}
