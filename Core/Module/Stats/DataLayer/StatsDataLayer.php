<?php

namespace Amora\Core\Module\Stats\Datalayer;

use Amora\Core\Database\MySqlDb;
use Amora\Core\Entity\Response\Pagination;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\DataLayerTrait;
use Amora\Core\Module\Stats\Model\EventRaw;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;
use Amora\Core\Value\QueryOrderDirection;

class StatsDataLayer
{
    use DataLayerTrait;

    const EVENT_RAW_TABLE_NAME = 'event_raw';

    public function __construct(
        private readonly MySqlDb $db,
    ) {}

    public function storeEvent(EventRaw $event): EventRaw
    {
        $res = $this->db->insert(self::EVENT_RAW_TABLE_NAME, $event->asArray());

        $event->id = $res;
        return $event;
    }

    public function filterEventsBy(
        array $ids = [],
        ?string $lockId = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        if (!isset($queryOptions)) {
            $queryOptions = new QueryOptions();
        }

        $orderByMapping = [
            'id' => 'm.id',
        ];

        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            'er.id AS event_raw_id',
            'er.user_id AS event_raw_user_id',
            'er.session_id AS event_raw_session_id',
            'er.created_at AS event_raw_created_at',
            'er.url AS event_raw_url',
            'er.referrer AS event_raw_referrer',
            'er.ip AS event_raw_ip',
            'er.user_agent AS event_raw_user_agent',
            'er.client_language AS event_raw_client_language',
            'er.processed_at AS event_raw_processed_at',
            'er.lock_id AS event_raw_lock_id',
        ];

        $joins = ' FROM ' . self::EVENT_RAW_TABLE_NAME . ' AS er';
        $where = ' WHERE 1';

        if ($ids) {
            $where .= $this->generateWhereSqlCodeForIds($params, $ids, 'er.id', 'eventRawId');
        }

        if ($lockId) {
            $where .= $this->generateWhereSqlCodeForIds($params, [$lockId], 'er.user_id', 'userId');
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = EventRaw::fromArray($item);
        }

        return $output;
    }

    public function getEntriesFromQueue(string $lockId, int $qty = 10000): array
    {
        return $this->filterEventsBy(
            lockId: $lockId,
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy(field: 'raw_id', direction: QueryOrderDirection::ASC)],
                pagination: new Pagination(itemsPerPage: $qty),
            ),
        );
    }

    public function getNumberOfLockedTransmissions(): int
    {
        $sql = '
            SELECT COUNT(*)
            FROM ' . self::EVENT_RAW_TABLE_NAME . '
            WHERE lock_id IS NOT NULL
        ';

        $res = $this->db->fetchColumn($sql);

        return (int)$res;
    }

    public function lockQueueEntries(string $lockId, int $qty = 10000): bool
    {
        $sql = '
            UPDATE ' . self::EVENT_RAW_TABLE_NAME . '
                SET lock_id = :lock_id,
                    processed_at = :processed_at
            WHERE processed_at IS NULL
                AND lock_id IS NULL
            ORDER BY id ASC
            LIMIT ' . $qty
        ;

        $params = [
            ':lock_id' => $lockId,
            ':processed_at' => DateUtil::getCurrentDateForMySql(),
        ];

        return $this->db->execute($sql, $params);
    }

    public function releaseQueueLocksIfNeeded($windowTimeInSeconds = 600): bool
    {
        $windowDate = date(DateUtil::MYSQL_DATETIME_FORMAT, time() - $windowTimeInSeconds);

        $sql = '
            UPDATE ' . self::EVENT_RAW_TABLE_NAME . '
                SET lock_id = NULL,
                    processed_at = NULL
            WHERE processed_at IS NOT NULL
                AND lock_id IS NOT NULL
                AND processed_at < :processed_at
        ';
        $params = [':processed_at' => $windowDate];

        return $this->db->execute($sql, $params);
    }

    public function getUniqueLockId(): string
    {
        $count = 0;

        $sql = '
            SELECT id
            FROM ' . self::EVENT_RAW_TABLE_NAME . '
            WHERE lock_id = :lockId
        ';

        do {
            $lockId = StringUtil::getRandomString(64);
            $params[':lockId'] = $lockId;
            $res = $this->db->fetchAll($sql, $params);
        } while (!empty($res) && $count++ < 5);

        return $lockId;
    }
}
