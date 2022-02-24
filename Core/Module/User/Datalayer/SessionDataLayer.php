<?php


namespace Amora\Core\Module\User\Datalayer;

use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\Logger;
use Amora\Core\Module\User\Model\Session;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\DateUtil;
use DateTimeZone;

class SessionDataLayer
{
    const SESSION_TABLE_NAME = 'session';

    public function __construct(
        private MySqlDb $db,
        private Logger $logger
    ) {}

    public function getSessionForSessionId(string $sessionId): ?Session
    {
        $sql = '
            SELECT
                s.id AS session_id,
                s.user_id AS user_id,
                s.sid,
                s.created_at AS session_created_at,
                s.last_visited_at,
                s.valid_until,
                s.forced_expiration_at,
                s.timezone,
                s.ip,
                s.browser_and_platform,
                u.language_id,
                u.role_id,
                u.journey_id,
                u.created_at AS user_created_at,
                u.updated_at AS user_updated_at,
                u.email,
                u.name,
                u.password_hash,
                u.bio,
                u.is_enabled,
                u.verified,
                u.timezone,
                u.change_email_to
            FROM ' . self::SESSION_TABLE_NAME . ' AS s
                JOIN ' . UserDataLayer::USER_TABLE . ' AS u
                    ON s.user_id = u.id
            WHERE s.sid = :session_id
        ';

        $params = [
            ':session_id' => $sessionId
        ];

        $res = $this->db->fetchOne($sql, $params);

        if (empty($res)) {
            return null;
        }

        $user = User::fromArray($res);

        return Session::fromArray($res, $user);
    }

    public function refreshSession(int $sessionId, string $newExpiryDate): bool
    {
        $res = $this->db->update(
            self::SESSION_TABLE_NAME,
            $sessionId,
            [
                'last_visited_at' => DateUtil::getCurrentDateForMySql(),
                'valid_until' => $newExpiryDate
            ]
        );

        if (!$res) {
            $this->logger->logError('Error refreshing session. ID: ' . $sessionId);
        }

        return $res;
    }

    public function expireSession(int $sessionId): bool
    {
        $res = $this->db->update(
            self::SESSION_TABLE_NAME,
            $sessionId,
            [
                'forced_expiration_at' => DateUtil::getCurrentDateForMySql()
            ]
        );

        if (!$res) {
            $this->logger->logError('Error expiring session. ID: ' . $sessionId);
        }

        return $res;
    }

    public function createNewSession(Session $session): ?Session
    {
        $data = $session->asArray();
        unset($data['id']);

        $res = $this->db->insert(self::SESSION_TABLE_NAME, $data);
        if (empty($res)) {
            $this->logger->logError('Error creating new session');
            return null;
        }

        return $session;
    }

    public function updateTimezoneForUserId(int $userId, DateTimeZone $newTimezone): bool
    {
        $res = $this->db->execute(
            '
                UPDATE ' . self::SESSION_TABLE_NAME . '
                SET timezone = :timezone
                WHERE user_id = :userId
                    AND valid_until < :now
                    AND forced_expiration_at IS NULL
            ',
            [
                ':timezone' => $newTimezone->getName(),
                ':userId' => $userId,
                ':now' => DateUtil::getCurrentDateForMySql()
            ]
        );

        if (!$res) {
            $this->logger->logError(
                'Error updating timezone. User ID: ' . $userId .
                ' - New timezone: ' . $newTimezone->getName()
            );
        }

        return $res;
    }
}
