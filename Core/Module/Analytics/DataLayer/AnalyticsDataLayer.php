<?php

namespace Amora\Core\Module\Analytics\Datalayer;

use Amora\Core\Database\MySqlDb;
use Amora\Core\Entity\Response\Pagination;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Analytics\Entity\PageView;
use Amora\Core\Module\Analytics\Entity\PageViewCount;
use Amora\Core\Module\Analytics\Model\EventValue;
use Amora\Core\Module\Analytics\Value\Parameter;
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

    const string EVENT_RAW_TABLE = 'event_raw';
    const string EVENT_PROCESSED_TABLE = 'event_processed';
    const string EVENT_TYPE_TABLE = 'event_type';
    const string EVENT_SEARCH_TABLE = 'event_search';

    const string EVENT_VALUE_LANGUAGE_ISO_CODE = 'event_value_language_iso_code';
    const string EVENT_VALUE_REFERRER = 'event_value_referrer';
    const string EVENT_VALUE_URL = 'event_value_url';
    const string EVENT_VALUE_USER_HASH = 'event_value_user_hash';
    const string EVENT_VALUE_USER_AGENT_PLATFORM = 'event_value_user_agent_platform';
    const string EVENT_VALUE_USER_AGENT_BROWSER = 'event_value_user_agent_browser';

    const string BOT_PATH_TABLE = 'bot_path';
    const string BOT_USER_AGENT = 'bot_user_agent';

    public function __construct(
        private readonly MySqlDb $db,
    ) {}

    public function storeEventRaw(EventRaw $event): ?EventRaw
    {
        $res = $this->db->insert(self::EVENT_RAW_TABLE, $event->asArray());

        if (!$res) {
            return null;
        }

        $event->id = $res;
        return $event;
    }

    public function storeEventProcessed(EventProcessed $event): void
    {
        $this->db->insert(self::EVENT_PROCESSED_TABLE, $event->asArray());
    }

    public function storeSearch(int $rawId, string $searchQuery): void
    {
        $this->db->insert(
            self::EVENT_SEARCH_TABLE,
            [
                'raw_id' => $rawId,
                'query' => $searchQuery,
            ],
        );
    }

    public function storeEventValue(EventValue $item, Parameter $parameter): EventValue
    {
        $res = $this->db->insert(
            tableName: $parameter->getValueTableName(),
            data: $item->asArray(),
        );

        $item->id = $res;

        return $item;
    }

    public function calculateCountAggregatedBy(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        AggregateBy $aggregateBy,
        ?EventType $eventType = null,
        ?Parameter $parameter = null,
        ?int $eventId = null,
        bool $includeVisitorHash = false,
    ): array {
        $dateFormat = DateUtil::getMySqlAggregateFormat($aggregateBy);

        $params = [];

        $innerSql = '';
        $whereSql = '';

        if ($eventType) {
            $whereSql .= ' AND ep.type_id = :eventTypeId';
            $params[':eventTypeId'] = $eventType->value;
        }

        $sqlCount = '*';
        if ($includeVisitorHash) {
            $innerSql .= ' INNER JOIN ' . Parameter::VisitorHash->getValueTableName() . ' AS ev ON ev.id = ep.' . Parameter::VisitorHash->getColumnName();
            $sqlCount = 'DISTINCT ep.' . Parameter::VisitorHash->getColumnName();
        }

        if ($eventId && $parameter) {
            $innerSql .= ' INNER JOIN ' . $parameter->getValueTableName() . ' AS evp ON evp.id = ep.' . $parameter->getColumnName();
            $whereSql .= ' AND evp.id = :eventId';
            $params[':eventId'] = $eventId;
        }

        $sql = "
            SELECT
                DATE_FORMAT(ep.created_at, $dateFormat) AS date_format,
                COUNT($sqlCount) AS cnt
            FROM " . self::EVENT_PROCESSED_TABLE . " AS ep
                $innerSql
            WHERE 1
                AND ep.created_at >= :createdAtFrom
                AND ep.created_at <= :createdAtTo
                $whereSql
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
                count: (int)$item['cnt'],
                date: DateUtil::convertPartialDateFormatToFullDate($item['date_format'], $aggregateBy),
            );
        }

        return $reportDataOutput;
    }

    public function calculateTotalAggregatedBy(
        Parameter $parameter,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        int $limit,
        ?EventType $eventType = null,
        ?Parameter $parameterQuery = null,
        ?int $eventId = null,
    ): array {
        $params = [];

        $innerSql = '';
        $whereSql = '';
        if ($eventType) {
            $whereSql .= ' AND ep.type_id = :eventTypeId';
            $params[':eventTypeId'] = $eventType->value;
        }

        if ($eventId && $parameterQuery) {
            $innerSql .= ' INNER JOIN ' . $parameterQuery->getValueTableName() . ' AS evp ON evp.id = ep.' . $parameterQuery->getColumnName();
            $whereSql .= ' AND evp.id = :eventId';
            $params[':eventId'] = $eventId;
        }

        $sql = "
            SELECT
                ev.id,
                ev.value,
                COUNT(*) AS cnt
            FROM " . self::EVENT_PROCESSED_TABLE . " AS ep
                LEFT JOIN {$parameter->getValueTableName()} AS ev ON ev.id = ep.{$parameter->getColumnName()}
                $innerSql
            WHERE 1
                AND ep.created_at >= :createdAtFrom
                AND ep.created_at <= :createdAtTo
                $whereSql
            GROUP BY
                ep.{$parameter->getColumnName()}
            ORDER BY
                cnt DESC
            LIMIT $limit;
        ";
        $params[':createdAtFrom'] = $from->format(DateUtil::MYSQL_DATETIME_FORMAT);
        $params[':createdAtTo'] = $to->format(DateUtil::MYSQL_DATETIME_FORMAT);

        $res = $this->db->fetchAll($sql, $params);

        $reportDataOutput = [];
        foreach ($res as $item) {
            $reportDataOutput[] = new PageViewCount(
                count: (int)$item['cnt'],
                id: (int)$item['id'],
                value: $item['value'] ?? '',
            );
        }

        return $reportDataOutput;
    }

    public function filterEventRawBy(
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
            'er.search_query AS event_raw_search_query',
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

    public function filterEventValueBy(
        Parameter $parameter,
        ?int $id = null,
        ?string $value = null,
    ): ?EventValue {
        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            'id AS event_value_id',
            'value AS event_value_value',
        ];

        $joins = ' FROM ' . $parameter->getValueTableName() . ' AS ev';
        $where = ' WHERE 1';

        if ($id) {
            $where .= ' AND id = :eventId';
            $params[':eventId'] = $id;
        }

        if ($value) {
            $where .= ' AND value = :eventValue';
            $params[':eventValue'] = $value;
        }

        $orderByAndLimit = ' ORDER BY id DESC';

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchOne($sql, $params);

        if (!$res) {
            return null;
        }

        return EventValue::fromArray($res);
    }

    public function getEntriesFromQueue(string $lockId, int $qty = 10000): array
    {
        return $this->filterEventRawBy(
            lockId: $lockId,
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy(field: 'raw_id', direction: QueryOrderDirection::ASC)],
                pagination: new Pagination(itemsPerPage: $qty),
            ),
        );
    }

    public function getLockedEntriesCount(): int
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
            $lockId = StringUtil::generateRandomString(64);
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

    public function loadBotPaths(): array
    {
        $bots = $this->db->fetchAll(
            'SELECT `id`, `path` FROM ' . self::BOT_PATH_TABLE,
        );

        $output = [];

        foreach ($bots as $bot) {
            if (empty($bot['path'])) {
                continue;
            }

            $output[$bot['path']] = true;
        }

        return $output;
    }

    public function loadBotUserAgents(): array
    {
        $bots = $this->db->fetchAll(
            'SELECT `id`, `name` FROM ' . self::BOT_USER_AGENT,
        );

        $output = [];

        foreach ($bots as $bot) {
            if (empty($bot['name'])) {
                continue;
            }

            $output[$bot['name']] = true;
        }

        return $output;
    }

    public function loadValues(Parameter $parameter): array
    {
        $items = $this->db->fetchAll(
            'SELECT `id`, `value` FROM ' . $parameter->getValueTableName(),
        );

        $output = [];

        foreach ($items as $item) {
            $output[$item['value']] = (int)$item['id'];
        }

        return $output;
    }

    public function destroyRawEvent(array $rawIds): bool
    {
        $params = [];
        $paramNames = [];

        foreach ($rawIds as $rawId) {
            if (!is_int($rawId)) {
                continue;
            }

            $paramName = ':rawId' . $rawId;
            $paramNames[] = $paramName;
            $params[$paramName] = $rawId;
        }

        return $this->db->execute(
            '
                DELETE FROM ' . self::EVENT_RAW_TABLE . '
                WHERE id IN (' . implode(', ', $paramNames) . ')
            ',
            $params,
        );
    }

    public function destroyOldEvents(): bool
    {
        $twoYearsAgo = DateUtil::convertStringToDateTimeImmutable('-2 years');

        return $this->db->execute(
            '
                DELETE FROM ' . self::EVENT_RAW_TABLE . '
                WHERE created_at = :createdAtBefore
                    AND processed_at IS NOT NULL
            ',
            [
                ':createdAtBefore' => $twoYearsAgo->format(DateUtil::MYSQL_DATETIME_FORMAT),
            ],
        );
    }
}
