<?php

namespace Amora\Core\Module\User\Service;

use DateTimeImmutable;
use DateTimeZone;
use UserAgentParserUtil;
use Amora\Core\Core;
use Amora\Core\Module\User\Datalayer\SessionDataLayer;
use Amora\Core\Module\User\Model\Session;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\StringUtil;

class SessionService
{
    const SESSION_ID_COOKIE_NAME = 'sid';
    const SESSION_ID_COOKIE_VALID_FOR_SECONDS = 2592000; // 30 days

    private ?Session $session = null;

    public function __construct(
        private readonly SessionDataLayer $dataLayer,
    ) {}

    public function generateSessionId(): string
    {
        return  date('YmdHis') . StringUtil::getRandomString(16);
    }

    private function generateNewValidUntil(?DateTimeImmutable $from = null): DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat(
            'U',
            $from
                ? $from->getTimestamp() + self::SESSION_ID_COOKIE_VALID_FOR_SECONDS
                : time() + self::SESSION_ID_COOKIE_VALID_FOR_SECONDS
        );
    }

    public function login(
        User $user,
        DateTimeZone $timezone,
        ?string $ip = null,
        ?string $userAgent = null,
    ): ?Session {
        $now = new DateTimeImmutable();
        $newValidUntil = $this->generateNewValidUntil($now);

        $session = new Session(
            id: null,
            sessionId: $this->generateSessionId(),
            user: $user,
            timezone: $timezone,
            createdAt: $now,
            lastVisitedAt: $now,
            validUntil: $this->generateNewValidUntil($now),
            forcedExpirationAt: null,
            ip: $ip,
            browserAndPlatform: UserAgentParserUtil::parse($userAgent)->getBrowserAndPlatform(),
        );

        $this->updateBrowserCookie($session->sessionId, $newValidUntil->getTimestamp());
        return $this->dataLayer->createNewSession($session);
    }

    public function logout(Session $session)
    {
        $this->updateBrowserCookie($session->getSessionId(), time() - 60 * 60 * 24);
        return $this->dataLayer->expireSession($session->getId());
    }

    private function updateBrowserCookie(string $sid, int $newExpiryTimestamp)
    {
        $isLive = Core::isRunningInLiveEnv();
        $options = [
            'expires' => $newExpiryTimestamp,
            'path' => '/',
            'secure' => $isLive,
            'httponly' => true,
            'samesite' => 'Strict',
        ];

        if ($isLive) {
            $options['domain'] = parse_url(Core::getConfigValue('baseUrl'), PHP_URL_HOST);
        }

        setcookie(self::SESSION_ID_COOKIE_NAME, $sid, $options);
        $_COOKIE[self::SESSION_ID_COOKIE_NAME] = $sid;
    }

    public function loadSession(?string $sessionId, bool $forceReload = false): ?Session
    {
        if (empty($sessionId)) {
            return null;
        }

        if (!$forceReload && !empty($this->session)) {
            return $this->session;
        }

        $this->session = $this->dataLayer->getSessionForSessionId($sessionId);
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
