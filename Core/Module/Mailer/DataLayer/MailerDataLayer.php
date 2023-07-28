<?php

namespace Amora\Core\Module\Mailer\Datalayer;

use Amora\Core\Database\MySqlDb;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\DataLayerTrait;
use Amora\Core\Module\Mailer\Model\MailerItem;
use Amora\Core\Module\Mailer\Model\MailerLogItem;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;

class MailerDataLayer
{
    use DataLayerTrait;

    const MAILER_QUEUE_TABLE_NAME = 'mailer_queue';
    const MAILER_LOG_TABLE_NAME = 'mailer_log';

    public function __construct(
        private readonly MySqlDb $db,
    ) {}

    public function filterMailerItemBy(
        array $ids = [],
        array $templateIds = [],
        ?bool $hasError = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        if (!isset($queryOptions)) {
            $queryOptions = new QueryOptions();
        }

        $orderByMapping = [
            'id' => 'q.id',
            'processed_at' => 'q.processed_at',
        ];

        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            'q.id AS mailer_item_id',
            'q.template_id AS mailer_item_template_id',
            'q.reply_to_email AS mailer_item_reply_to_email',
            'q.sender_name AS mailer_item_sender_name',
            'q.receiver_email AS mailer_item_receiver_email',
            'q.receiver_name AS mailer_item_receiver_name',
            'q.subject AS mailer_item_subject',
            'q.content_html AS mailer_item_content_html',
            'q.fields_json AS mailer_item_fields_json',
            'q.created_at AS mailer_item_created_at',
            'q.processed_at AS mailer_item_processed_at',
            'q.has_error AS mailer_item_has_error',
            'q.lock_id AS mailer_item_lock_id',
        ];

        $joins = ' FROM ' . self::MAILER_QUEUE_TABLE_NAME . ' AS q';
        $where = ' WHERE 1';

        if ($ids) {
            $where .= $this->generateWhereSqlCodeForIds($params, $ids, 'q.id', 'mailerItemId');
        }

        if ($templateIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $templateIds, 'q.template_id', 'templateId');
        }

        if (isset($hasError)) {
            $where .= ' AND q.has_error = :hasError';
            $params[':hasError'] = $hasError ? '1' : '0';
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = MailerItem::fromArray($item);
        }

        return $output;
    }

    private function getMailerQueue(
        ?string $lockId = null,
        ?int $limit = null,
        string $orderDirection = 'ASC'
    ): array {
        if ($orderDirection != 'ASC' || $orderDirection != 'DESC') {
            $orderDirection = 'ASC';
        }

        $params = [];
        $sql = '
            SELECT
                q.id AS mailer_item_id,
                q.template_id AS mailer_item_template_id,
                q.reply_to_email AS mailer_item_reply_to_email,
                q.sender_name AS mailer_item_sender_name,
                q.receiver_email AS mailer_item_receiver_email,
                q.receiver_name AS mailer_item_receiver_name,
                q.subject AS mailer_item_subject,
                q.content_html AS mailer_item_content_html,
                q.fields_json AS mailer_item_fields_json,
                q.created_at AS mailer_item_created_at,
                q.processed_at AS mailer_item_processed_at,
                q.has_error AS mailer_item_has_error,
                q.lock_id AS mailer_item_lock_id
            FROM ' . self::MAILER_QUEUE_TABLE_NAME . ' AS q
            WHERE 1
        ';

        if ($lockId) {
            $sql .= ' AND q.lock_id = :lock_id';
            $params[':lock_id'] = $lockId;
        }

        $sql .= ' ORDER BY q.id ' . $orderDirection;

        if (isset($limit)) {
            $sql .= ' LIMIT ' . $limit;
        }

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = MailerItem::fromArray($item);
        }

        return $output;
    }

    public function storeMail(MailerItem $mailerItem): MailerItem
    {
        $res = $this->db->insert(self::MAILER_QUEUE_TABLE_NAME, $mailerItem->asArray());

        $mailerItem->id = (int)$res;
        return $mailerItem;
    }

    public function getMailsFromQueue(string $lockId, int $qty = 1000): array
    {
        return $this->getMailerQueue($lockId, $qty, 'DESC');
    }

    public function lockMailsInQueue(string $lockId, int $qty = 1000): bool
    {
        $sql = '
            UPDATE ' . self::MAILER_QUEUE_TABLE_NAME . '
                SET lock_id = :lock_id,
                    processed_at = :processed_at
            WHERE processed_at IS NULL
                AND lock_id IS NULL
            ORDER BY id DESC
            LIMIT ' . $qty
        ;

        $params = [
            ':lock_id' => $lockId,
            ':processed_at' => DateUtil::getCurrentDateForMySql()
        ];

        return $this->db->execute($sql, $params);
    }

    public function releaseMailerQueueLocksIfNeeded($windowTimeInSeconds = 600): bool
    {
        $windowDate = date('Y-m-d H:i:s', time() - $windowTimeInSeconds);

        $sql = '
            UPDATE ' . self::MAILER_QUEUE_TABLE_NAME . '
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
            FROM ' . self::MAILER_QUEUE_TABLE_NAME . '
            WHERE lock_id = :lock_id
        ';

        do {
            $lockId = StringUtil::generateRandomString(64);
            $params[':lock_id'] = $lockId;
            $res = $this->db->fetchAll($sql, $params);
        } while (!empty($res) && $count++ < 5);

        return $lockId;
    }

    public function markMailAsProcessed(MailerItem $mailerItem, bool $hasError = false): bool
    {
        $sql = '
            UPDATE ' . self::MAILER_QUEUE_TABLE_NAME . '
            SET lock_id = NULL,
                has_error = :error
            WHERE id = :id
        ';
        $params = [
            ':id' => $mailerItem->id,
            ':error' => empty($hasError) ? 0 : 1
        ];

        return $this->db->execute($sql, $params);
    }

    public function storeMailerLog(MailerLogItem $item): MailerLogItem
    {
        $res = $this->db->insert(self::MAILER_LOG_TABLE_NAME, $item->asArray());

        $item->id = (int)$res;
        return $item;
    }

    public function updateMailerLog(
        int $id,
        string $response,
        ?string $errorMessage = null,
        $sent = true
    ): bool {
        $sql = '
            UPDATE ' . self::MAILER_LOG_TABLE_NAME . '
            SET sent = :sent,
                response = :response,
                error_message = :error
            WHERE id = :id
        ';
        $params = [
            ':id' => $id,
            ':response' => $response,
            ':sent' => $sent,
            ':error' => $errorMessage
        ];

        return $this->db->execute($sql, $params);
    }
}
