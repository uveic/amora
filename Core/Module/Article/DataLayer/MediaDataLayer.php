<?php

namespace Amora\Core\Module\Article\Datalayer;

use Amora\Core\Database\MySqlDb;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\User\DataLayer\UserDataLayer;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Logger;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\DataLayerTrait;
use Amora\Core\Value\QueryOrderDirection;

class MediaDataLayer
{
    use DataLayerTrait;

    const MEDIA_TABLE = 'core_media';
    const MEDIA_TYPE_TABLE = 'core_media_type';
    const MEDIA_STATUS_TABLE = 'core_media_status';

    public function __construct(
        private readonly MySqlDb $db,
        private readonly Logger $logger,
    ) {}

    public function getDb(): MySqlDb
    {
        return $this->db;
    }

    public function filterMediaBy(
        array $ids = [],
        array $userIds = [],
        array $typeIds = [],
        array $statusIds = [],
        ?int $fromId = null,
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
            'm.id AS media_id',
            'm.user_id',
            'm.type_id AS media_type_id',
            'm.status_id AS media_status_id',
            'm.path AS media_path',
            'm.filename_original AS media_filename_original',
            'm.filename_small AS media_filename_small',
            'm.filename_medium AS media_filename_medium',
            'm.filename_large AS media_filename_large',
            'm.caption_html AS media_caption_html',
            'm.created_at AS media_created_at',
            'm.updated_at AS media_updated_at',

            'u.language_iso_code AS user_language_iso_code',
            'u.role_id AS user_role_id',
            'u.journey_id AS user_journey_id',
            'u.created_at AS user_created_at',
            'u.updated_at AS user_updated_at',
            'u.email AS user_email',
            'u.name AS user_name',
            'u.password_hash AS user_password_hash',
            'u.bio AS user_bio',
            'u.is_enabled AS user_is_enabled',
            'u.verified AS user_verified',
            'u.timezone AS user_timezone',
            'u.change_email_to AS user_change_email_to',
        ];

        $joins = ' FROM ' . self::MEDIA_TABLE . ' AS m';
        $joins .= ' LEFT JOIN ' . UserDataLayer::USER_TABLE . ' AS u ON u.id = m.user_id';
        $where = ' WHERE 1';

        if ($ids) {
            $where .= $this->generateWhereSqlCodeForIds($params, $ids, 'm.id', 'mediaId');
        }

        if ($userIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $userIds, 'm.user_id', 'userId');
        }

        if ($typeIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $typeIds, 'm.type_id', 'typeId');
        }

        if ($statusIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $statusIds, 'm.status_id', 'statusId');
        }

        if (isset($fromId)) {
            $direction = isset($queryOptions->orderBy[0])
                ? ($queryOptions->orderBy[0]->direction === QueryOrderDirection::ASC ? '>' : '<')
                : '>';
            $where .= ' AND m.id ' . $direction . ' :fromId';
            $params[':fromId'] = $fromId;
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = Media::fromArray($item);
        }

        return $output;
    }

    public function storeFile(Media $data): Media
    {
        $resInsert = $this->db->insert(self::MEDIA_TABLE, $data->asArray());

        if (empty($resInsert)) {
            $this->logger->logError('Error inserting media');
        }

        $data->id = (int)$resInsert;

        return $data;
    }

    public function markMediaAsDeleted(int $id): bool
    {
        return $this->db->execute(
            '
                UPDATE ' . self::MEDIA_TABLE .
            '   SET status_id = :statusId,
                    updated_at = :updatedAt
                WHERE id = :id
            ',
            [
                ':id' => $id,
                ':statusId' => MediaStatus::Deleted->value,
                ':updatedAt' => DateUtil::getCurrentDateForMySql(),
            ],
        );
    }

    public function destroyMedia(int $id): bool
    {
        return $this->db->delete(
            tableName: self::MEDIA_TABLE,
            where: ['id' => $id],
        );
    }

    public function getTotalMedia(): array
    {
        $output = [];
        $res = $this->db->fetchAll(
            '
                SELECT
                    m.type_id,
                    COUNT(*) AS total
                FROM ' . self::MEDIA_TABLE . ' AS m
                WHERE m.status_id IN (:active)
                GROUP BY m.type_id;
            ',
            [
                ':active' => MediaStatus::Active->value,
            ]
        );

        foreach ($res as $item) {
            $output[(int)$item['type_id']] = (int)$item['total'];
        }

        return $output;
    }
}
