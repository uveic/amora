<?php

namespace uve\core\module\user\service;

use UserAgentParserUtil;
use uve\core\Logger;
use uve\core\module\user\dataLayer\SessionDataLayer;
use uve\core\module\user\model\Session;
use uve\core\module\user\model\User;
use uve\core\util\DateUtil;
use uve\core\util\StringUtil;

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
    ): bool {
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
        $res = $this->dataLayer->createNewSession($session);

        return $res ? true : false;
    }

    public function logout(Session $session)
    {
        $this->updateBrowserCookie($session->getSessionId(), time() - 60 * 60 * 24);
        return $this->dataLayer->expireSession($session->getId());
    }

    private function updateBrowserCookie(string $sid, string $newExpiryTimestamp)
    {
        setcookie(
            self::SESSION_ID_COOKIE_NAME,
            $sid,
            $newExpiryTimestamp,
            '/'
        );
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
