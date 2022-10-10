<?php

namespace Amora\Core\Module\Analytics\Datalayer;

use Amora\Core\Database\MySqlDb;
use Amora\Core\Entity\Response\Pagination;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Analytics\Entity\PageView;
use Amora\Core\Module\Analytics\Entity\PageViewCount;
use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Module\DataLayerTrait;
use Amora\Core\Module\Analytics\Model\EventProcessed;
use Amora\Core\Module\Analytics\Model\EventRaw;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;
use Amora\Core\Value\AggregateBy;
use Amora\Core\Value\QueryOrderDirection;
use DateTimeImmutable;

class AnalyticsDataLayer
{
    use DataLayerTrait;

    const EVENT_RAW_TABLE = 'event_raw';
    const EVENT_PROCESSED_TABLE = 'event_processed';
    const EVENT_TYPE_TABLE = 'event_type';

    public function __construct(
        private readonly MySqlDb $db,
    ) {}

    public function storeEventRaw(EventRaw $event): ?EventRaw
    {
        $res = $this->db->insert(self::EVENT_RAW_TABLE, $event->asArray());

        if (empty($res)) {
            return null;
        }

        $event->id = $res;
        return $event;
    }

    public function storeEventProcessed(EventProcessed $event): ?EventProcessed
    {
        $res = $this->db->insert(self::EVENT_PROCESSED_TABLE, $event->asArray());

        if (empty($res)) {
            return null;
        }

        $event->id = $res;
        return $event;
    }

    public function filterPageViewsBy(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        AggregateBy $aggregateBy,
        ?EventType $eventType = null,
    ): array {
        $dateFormat = DateUtil::getMySqlAggregateFormat($aggregateBy);

        $params = [];

        $typeSql = '';
        if ($eventType) {
            $typeSql = ' AND ep.type_id = :eventTypeId';
            $params[':eventTypeId'] = $eventType->value;
        }

        $sql = "
            SELECT
                DATE_FORMAT(er.created_at, $dateFormat) AS date_format,
                COUNT(*) AS count
            FROM " . self::EVENT_PROCESSED_TABLE . " AS ep
                INNER JOIN " . self::EVENT_RAW_TABLE . " AS er ON er.id = ep.raw_id
            WHERE 1
                AND er.created_at >= :createdAtFrom
                AND er.created_at <= :createdAtTo
                " . $typeSql . "
            GROUP BY
                date_format
            ORDER BY
                date_format ASC;
        ";
        $params[':createdAtFrom'] = $from->format(DateUtil::MYSQL_DATETIME_FORMAT);
        $params[':createdAtTo'] = $to->format(DateUtil::MYSQL_DATETIME_FORMAT);

        $res = $this->db->fetchAll($sql, $params);

        $reportDataOutput = [];
        foreach ($res as $item) {
            $reportDataOutput[] = new PageView(
                count: (int)$item['count'],
                date: DateUtil::convertPartialDateFormatToFullDate($item['date_format'], $aggregateBy),
            );
        }

        return $reportDataOutput;
    }

    public function countTopPages(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        ?EventType $eventType = null,
    ): array {
        $params = [];

        $typeSql = '';
        if ($eventType) {
            $typeSql = ' AND ep.type_id = :eventTypeId';
            $params[':eventTypeId'] = $eventType->value;
        }

        $sql = "
            SELECT
                er.url,
                COUNT(*) AS count
            FROM " . self::EVENT_PROCESSED_TABLE . " AS ep
                INNER JOIN " . self::EVENT_RAW_TABLE . " AS er ON er.id = ep.raw_id
            WHERE 1
                AND er.created_at >= :createdAtFrom
                AND er.created_at <= :createdAtTo
                " . $typeSql . "
            GROUP BY
                er.url
            ORDER BY
                count DESC;
        ";
        $params[':createdAtFrom'] = $from->format(DateUtil::MYSQL_DATETIME_FORMAT);
        $params[':createdAtTo'] = $to->format(DateUtil::MYSQL_DATETIME_FORMAT);

        $res = $this->db->fetchAll($sql, $params);

        $reportDataOutput = [];
        foreach ($res as $item) {
            $reportDataOutput[] = new PageViewCount(
                count: (int)$item['count'],
                name: $item['url'],
            );
        }

        return $reportDataOutput;
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
            'raw_id' => 'er.id',
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

        $joins = ' FROM ' . self::EVENT_RAW_TABLE . ' AS er';
        $where = ' WHERE 1';

        if ($ids) {
            $where .= $this->generateWhereSqlCodeForIds($params, $ids, 'er.id', 'eventRawId');
        }

        if ($lockId) {
            $where .= $this->generateWhereSqlCodeForIds($params, [$lockId], 'er.lock_id', 'lockId');
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

    public function getNumberOfLockedEntries(): int
    {
        $sql = '
            SELECT COUNT(*)
            FROM ' . self::EVENT_RAW_TABLE . '
            WHERE lock_id IS NOT NULL
        ';

        $res = $this->db->fetchColumn($sql);

        return (int)$res;
    }

    public function lockQueueEntries(string $lockId, int $qty = 10000): bool
    {
        $sql = '
            UPDATE ' . self::EVENT_RAW_TABLE . '
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
            UPDATE ' . self::EVENT_RAW_TABLE . '
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
            FROM ' . self::EVENT_RAW_TABLE . '
            WHERE lock_id = :lockId
        ';

        do {
            $lockId = StringUtil::getRandomString(64);
            $params[':lockId'] = $lockId;
            $res = $this->db->fetchAll($sql, $params);
        } while (!empty($res) && $count++ < 5);

        return $lockId;
    }

    public function markEventAsProcessed(int $rawEventId): bool
    {
        $params = [
            ':id' => $rawEventId,
        ];
        $sql = 'UPDATE ' . self::EVENT_RAW_TABLE . ' SET lock_id = NULL WHERE id = :id';

        return $this->db->execute($sql, $params);
    }
}