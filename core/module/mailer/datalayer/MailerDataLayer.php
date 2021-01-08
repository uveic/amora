<?php

namespace uve\core\module\mailer\datalayer;

use uve\core\database\MySqlDb;
use uve\core\Logger;
use uve\core\module\mailer\model\MailerItem;
use uve\core\module\mailer\model\MailerLogItem;
use uve\core\util\DateUtil;
use uve\core\util\StringUtil;

class MailerDataLayer
{
    const MAILER_QUEUE_TABLE_NAME = 'mailer_queue';
    const MAILER_LOG_TABLE_NAME = 'mailer_log';

    private MySqlDb $db;
    private Logger $logger;

    public function __construct(MySqlDb $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
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
                q.id,
                q.template_id,
                q.reply_to_email,
                q.sender_name,
                q.receiver_email,
                q.receiver_name,
                q.subject,
                q.content_html,
                q.fields_json,
                q.created_at,
                q.processed_at,
                q.has_error,
                q.lock_id
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

        $mailerItem->setId($res);
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
            $lockId = StringUtil::getRandomString(64);
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
            ':id' => $mailerItem->getId(),
            ':error' => empty($hasError) ? 0 : 1
        ];

        return $this->db->execute($sql, $params);
    }

    public function storeMailerLog(MailerLogItem $item): MailerLogItem
    {
        $res = $this->db->insert(self::MAILER_LOG_TABLE_NAME, $item->asArray());

        $item->setId($res);
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
