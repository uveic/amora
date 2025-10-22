<?php


namespace Amora\Core\Module\User\Datalayer;

use Amora\Core\Database\MySqlDb;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\DataLayerTrait;
use Amora\Core\Util\Logger;
use Amora\Core\Module\User\Model\Session;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;
use DateTimeZone;

class SessionDataLayer
{
    use DataLayerTrait;

    const string SESSION_TABLE_NAME = 'core_session';

    public function __construct(
        private readonly MySqlDb $db,
        private readonly Logger $logger,
    ) {}

    public function filterSessionBy(
        array $sessionIds = [],
        array $userIds = [],
        ?bool $isExpired = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        if (!isset($queryOptions)) {
            $queryOptions = new QueryOptions();
        }

        $orderByMapping = [
            'id' => 's.id',
            'last_visited_at' => 's.last_visited_at',
            'user_id' => 's.user_id',
        ];

        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            's.id AS session_id',
            's.user_id',
            's.sid AS session_sid',
            's.created_at AS session_created_at',
            's.last_visited_at AS session_last_visited_at',
            's.valid_until AS session_valid_until',
            's.forced_expiration_at AS session_forced_expiration_at',
            's.timezone AS session_timezone',
            's.ip AS session_ip',
            's.browser_and_platform AS session_browser_and_platform',

            'u.status_id AS user_status_id',
            'u.language_iso_code AS user_language_iso_code',
            'u.role_id AS user_role_id',
            'u.journey_id AS user_journey_id',
            'u.created_at AS user_created_at',
            'u.updated_at AS user_updated_at',
            'u.email AS user_email',
            'u.name AS user_name',
            'u.password_hash AS user_password_hash',
            'u.bio AS user_bio',
            'u.identifier AS user_identifier',
            'u.timezone AS user_timezone',
            'u.change_email_to AS user_change_email_to',
        ];

        $joins = ' FROM ' . self::SESSION_TABLE_NAME . ' AS s';
        $joins .= ' INNER JOIN ' . UserDataLayer::USER_TABLE . ' AS u ON s.user_id = u.id';

        $where = ' WHERE 1';

        if ($sessionIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $sessionIds, 's.sid', 'sessionId');
        }

        if ($userIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $userIds, 's.user_id', 'user_id');
        }

        if (isset($isExpired)) {
            $params[':now'] = DateUtil::getCurrentDateForMySql();
            $where .= ' AND s.forced_expiration_at IS NOT NULL OR s.valid_until <= :now';
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = Session::fromArray($item);
        }

        return $output;
    }

    public function updateSessionExpiryDateAndValidUntil(int $sessionId, DateTimeImmutable $newExpiryDate): bool
    {
        $res = $this->db->update(
            tableName: self::SESSION_TABLE_NAME,
            id: $sessionId,
            data: [
                'last_visited_at' => DateUtil::getCurrentDateForMySql(),
                'valid_until' => $newExpiryDate->format(DateUtil::MYSQL_DATETIME_FORMAT),
            ],
        );

        if (!$res) {
            $this->logger->logError('Error refreshing session. ID: ' . $sessionId);
        }

        return $res;
    }

    public function expireSession(int $sessionId): bool
    {
        $res = $this->db->update(
            tableName: self::SESSION_TABLE_NAME,
            id: $sessionId,
            data: [
                'forced_expiration_at' => DateUtil::getCurrentDateForMySql()
            ],
        );

        if (!$res) {
            $this->logger->logError('Error expiring session. ID: ' . $sessionId);
        }

        return $res;
    }

    public function expireAllSessionsForUser(int $userId): bool
    {
        return $this->db->execute(
            '
                UPDATE ' . self::SESSION_TABLE_NAME . '
                SET `forced_expiration_at` = :forcedExpirationAt
                WHERE `user_id` = :userId
                    AND `forced_expiration_at` IS NULL
            ',
            [
                ':forcedExpirationAt' => DateUtil::getCurrentDateForMySql(),
                ':userId' => $userId,
            ],
        );
    }

    public function createNewSession(Session $session): ?Session
    {
        $data = $session->asArray();
        unset($data['id']);

        $res = $this->db->insert(self::SESSION_TABLE_NAME, $data);
        if (!$res) {
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
