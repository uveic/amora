<?php

namespace Amora\Core\Module\Article\Datalayer;

use Amora\Core\Database\MySqlDb;
use Amora\Core\Module\Article\Model\ImageExif;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Model\MediaDestroyed;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\User\DataLayer\UserDataLayer;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Logger;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\DataLayerTrait;
use Amora\Core\Value\QueryOrderDirection;
use DateTimeImmutable;

class MediaDataLayer
{
    use DataLayerTrait;

    public const string MEDIA_TABLE = 'core_media';
    public const string MEDIA_TYPE_TABLE = 'core_media_type';
    public const string MEDIA_STATUS_TABLE = 'core_media_status';
    public const string MEDIA_DESTROYED_TABLE = 'core_media_destroyed';
    public const string MEDIA_EXIF_TABLE = 'core_media_exif';

    public function __construct(
        private readonly MySqlDb $db,
        private readonly Logger $logger,
    ) {
    }

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
        ?DateTimeImmutable $uploadedToS3Before = null,
        ?bool $isUploadedToS3 = null,
        ?bool $isDeletedLocally = null,
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
            'm.user_id AS media_user_id',
            'm.type_id AS media_type_id',
            'm.status_id AS media_status_id',
            'm.width_original AS media_width_original',
            'm.height_original AS media_height_original',
            'm.path AS media_path',
            'm.filename AS media_filename',
            'm.filename_extra_small AS media_filename_extra_small',
            'm.filename_small AS media_filename_small',
            'm.filename_medium AS media_filename_medium',
            'm.filename_large AS media_filename_large',
            'm.filename_extra_large AS media_filename_extra_large',
            'm.caption_html AS media_caption_html',
            'm.filename_source AS media_filename_source',
            'm.created_at AS media_created_at',
            'm.updated_at AS media_updated_at',
            'm.uploaded_to_s3_at AS media_uploaded_to_s3_at',
            'm.deleted_locally_at AS media_deleted_locally_at',

            'me.media_id AS media_exif_media_id',
            'me.width AS media_exif_width',
            'me.height AS media_exif_height',
            'me.size_bytes AS media_exif_size_bytes',
            'me.camera_model AS media_exif_camera_model',
            'me.taken_at AS media_exif_taken_at',
            'me.exposure_time AS media_exif_exposure_time',
            'me.iso AS media_exif_iso',

            'u.id AS user_id',
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

        $joins = ' FROM ' . self::MEDIA_TABLE . ' AS m';
        $joins .= ' LEFT JOIN ' . self::MEDIA_EXIF_TABLE . ' AS me ON me.media_id = m.id';
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
                ? ($queryOptions->orderBy[0]->direction === QueryOrderDirection::ASC ? '>=' : '<=')
                : '>';
            $where .= ' AND m.id ' . $direction . ' :fromId';
            $params[':fromId'] = $fromId;
        }

        if ($uploadedToS3Before) {
            $where .= ' AND m.uploaded_to_s3_at <= :uploadedToS3Before';
            $params[':uploadedToS3Before'] = $uploadedToS3Before->format(DateUtil::MYSQL_DATETIME_FORMAT);
        }

        if (isset($isUploadedToS3)) {
            $where .= ' AND m.uploaded_to_s3_at ' . ($isUploadedToS3 ? ' IS NOT NULL' : ' IS NULL');
        }

        if (isset($isDeletedLocally)) {
            $where .= ' AND m.deleted_locally_at ' . ($isDeletedLocally ? ' IS NOT NULL' : ' IS NULL');
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

    public function storeMedia(Media $data): ?Media
    {
        $resInsert = $this->db->insert(self::MEDIA_TABLE, $data->asArray());

        if (!$resInsert) {
            $this->logger->logError('Error inserting media');
            return null;
        }

        $data->id = $resInsert;

        $this->storeMediaExif($data->id, $data->exif);

        return $data;
    }

    public function storeMediaExif(int $mediaId, ?ImageExif $exif): void
    {
        if (!$exif || $exif->isEmpty()) {
            return;
        }

        $this->db->insert(
            self::MEDIA_EXIF_TABLE,
            array_merge(['media_id' => $mediaId], $exif->asArray()),
        );
    }

    public function storeMediaDestroyed(MediaDestroyed $data): MediaDestroyed
    {
        $resInsert = $this->db->insert(self::MEDIA_DESTROYED_TABLE, $data->asArray());

        if (!$resInsert) {
            $this->logger->logError('Error inserting media destroyed');
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

    public function updateMedia(Media $media): bool
    {
        return $this->db->update(
            tableName: self::MEDIA_TABLE,
            id: $media->id,
            data: $media->asArray(),
        );
    }

    public function updateMediaFields(
        int $mediaId,
        ?string $caption = null,
        ?DateTimeImmutable $uploadedToS3At = null,
        ?DateTimeImmutable $deletedLocallyAt = null,
    ): bool {
        $params = [];
        $fields = [];

        if ($caption) {
            $fields[] = 'caption_html = :caption';
            $params[':caption'] = $caption;
        }

        if ($uploadedToS3At) {
            $fields[] = 'uploaded_to_s3_at = :uploadedToS3At';
            $params[':uploadedToS3At'] = $uploadedToS3At->format(DateUtil::MYSQL_DATETIME_FORMAT);
        }

        if ($deletedLocallyAt) {
            $fields[] = 'deleted_locally_at = :deletedLocallyAt';
            $params[':deletedLocallyAt'] = $deletedLocallyAt->format(DateUtil::MYSQL_DATETIME_FORMAT);
        }

        if (!$params) {
            return true;
        }

        $params[':mediaId'] = $mediaId;
        $params[':updatedAt'] = DateUtil::getCurrentDateForMySql();
        $fields[] = 'updated_at = :updatedAt';

        $sql = '
            UPDATE ' . self::MEDIA_TABLE . '
            SET ' . implode(',', $fields) . '
            WHERE id = :mediaId
        ';

        return $this->db->execute($sql, $params);
    }

    public function destroyMedia(int $id): bool
    {
        $this->db->delete(
            tableName: self::MEDIA_EXIF_TABLE,
            where: ['media_id' => $id],
        );

        return $this->db->delete(
            tableName: self::MEDIA_TABLE,
            where: ['id' => $id],
        );
    }

    public function getMediaCountByTypeId(): array
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
