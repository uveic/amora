<?php

namespace Amora\Core\Module\User\Service;

use UserAgentParserUtil;
use Amora\Core\Core;
use Amora\Core\Logger;
use Amora\Core\Module\User\Datalayer\SessionDataLayer;
use Amora\Core\Module\User\Model\Session;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;

class SessionService
{
    const SESSION_ID_COOKIE_NAME = 'sid';
    const SESSION_ID_COOKIE_VALID_FOR_SECONDS = 2592000; // 30 days

    private ?Session $session = null;
    private SessionDataLayer $dataLayer;
    private Logger $logger;

    public function __construct(SessionDataLayer $dataLayer, Logger $logger)
    {
        $this->dataLayer = $dataLayer;
        $this->logger = $logger;
    }

    public function generateSessionId(): string
    {
        return  date('YmdHis') . StringUtil::getRandomString(16);
    }

    public function generateNewValidUntil(?string $from = null): int
    {
        if (empty($from)) {
            $from = DateUtil::getCurrentDateForMySql();
        } elseif (!DateUtil::isValidDateForMySql($from)) {
            $this->logger->logError('From date not valid: ' . $from);
            $from = DateUtil::getCurrentDateForMySql();
        }

        $fromTimestamp = strtotime($from);
        return $fromTimestamp + self::SESSION_ID_COOKIE_VALID_FOR_SECONDS;
    }

    public function login(
        User $user,
        string $timezone,
        ?string $ip = null,
        ?string $userAgent = null
    ): ?Session {
        $now = DateUtil::getCurrentDateForMySql();
        $newValidUntilTimestamp = $this->generateNewValidUntil($now);
        $session = new Session(
            null,
            $this->generateSessionId(),
            $user,
            $now,
            $now,
            DateUtil::getMySqlDateFromUnixTime($newValidUntilTimestamp),
            null,
            $timezone,
            $ip,
            UserAgentParserUtil::parse($userAgent)->getBrowserAndPlatform()
        );

        $this->updateBrowserCookie($session->getSessionId(), $newValidUntilTimestamp);
        return $this->dataLayer->createNewSession($session);
    }

    public function logout(Session $session)
    {
        $this->updateBrowserCookie($session->getSessionId(), time() - 60 * 60 * 24);
        return $this->dataLayer->expireSession($session->getId());
    }

    private function updateBrowserCookie(string $sid, string $newExpiryTimestamp)
    {
        $isLive = Core::isRunningInLiveEnv();
        $options = [
            'expires' => $newExpiryTimestamp,
            'path' => '/',
            'secure' => $isLive ? true : false,
            'httponly' => true,
            'samesite' => 'Strict',
        ];

        if ($isLive) {
            $options['domain'] = '.' . parse_url(Core::getConfigValue('baseUrl'), PHP_URL_HOST);
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

        return $this->session;
    }

    public function updateTimezoneForUserId(int $userId, string $newTimezone): bool
    {
        return $this->dataLayer->updateTimezoneForUserId($userId, $newTimezone);
    }
}
