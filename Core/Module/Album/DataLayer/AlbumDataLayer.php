<?php

namespace Amora\Core\Module\Album\Datalayer;

use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Model\Collection;
use Amora\Core\Module\Album\Model\CollectionMedia;
use Amora\Core\Module\Album\Model\AlbumSlug;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Module\Article\Datalayer\MediaDataLayer;
use Amora\Core\Module\DataLayerTrait;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Logger;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\User\DataLayer\UserDataLayer;
use Amora\Core\Util\StringUtil;
use Amora\Core\Value\QueryOrderDirection;

class AlbumDataLayer
{
    use DataLayerTrait;

    public const string ALBUM_TABLE = 'core_album';
    public const string COLLECTION_TABLE = 'core_collection';
    public const string COLLECTION_MEDIA_TABLE = 'core_collection_media';
    public const string ALBUM_SLUG_TABLE = 'core_album_slug';

    public const string ALBUM_STATUS_TABLE = 'core_album_status';
    public const string ALBUM_TEMPLATE_TABLE = 'core_album_template';

    public function __construct(
        private readonly MySqlDb $db,
        private readonly Logger $logger,
        private readonly MediaDataLayer $mediaDataLayer,
    ) {
    }

    public function getDb(): MySqlDb
    {
        return $this->db;
    }

    public function filterAlbumBy(
        array $albumIds = [],
        array $languageIsoCodes = [],
        array $statusIds = [],
        array $templateIds = [],
        array $mediaIds = [],
        ?string $slug = null,
        ?string $searchQuery = null,
        bool $includeCollections = false,
        bool $includeMedia = false,
        ?QueryOptions $queryOptions = null,
    ): array {
        if (!isset($queryOptions)) {
            $queryOptions = new QueryOptions();
        }

        $orderByMapping = [
            'updated_at' => 'a.updated_at',
            'begins_with' => 'begins_with',
            'word_begins_with' => 'word_begins_with',
            'title_contains' => 'title_contains',
        ];

        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            'a.id AS album_id',
            'a.language_iso_code AS album_language_iso_code',
            'a.user_id AS album_user_id',
            'a.status_id AS album_status_id',
            'a.main_media_id AS album_main_media_id',
            'a.template_id AS album_template_id',
            'a.created_at AS album_created_at',
            'a.updated_at AS album_updated_at',
            'a.title_html AS album_title_html',
            'a.content_html AS album_content_html',

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

            'm.id AS media_id',
            'm.user_id AS media_user_id',
            'm.type_id AS media_type_id',
            'm.status_id AS media_status_id',
            'm.width_original AS media_width_original',
            'm.height_original AS media_height_original',
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

            'als.id AS album_slug_id',
            'als.album_id AS album_slug_album_id',
            'als.created_at AS album_slug_created_at',
            'als.slug AS album_slug_slug',
        ];

        $joins = ' FROM ' . self::ALBUM_TABLE . ' AS a';
        $joins .= ' INNER JOIN ' . UserDataLayer::USER_TABLE . ' AS u ON u.id = a.user_id';
        $joins .= ' INNER JOIN ' . MediaDataLayer::MEDIA_TABLE . ' AS m ON m.id = a.main_media_id';
        $joins .= ' LEFT JOIN ' . MediaDataLayer::MEDIA_EXIF_TABLE . ' AS me ON me.media_id = m.id';
        $joins .= ' INNER JOIN ' . self::ALBUM_SLUG_TABLE . ' AS als ON als.id = a.slug_id';

        $where = ' WHERE 1';

        if ($albumIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $albumIds, 'a.id', 'albumId');
        }

        if ($statusIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $statusIds, 'a.status_id', 'statusId');
        }

        if ($languageIsoCodes) {
            $where .= $this->generateWhereSqlCodeForIds(
                params: $params,
                ids: $languageIsoCodes,
                dbColumnName: 'a.language_iso_code',
                keyName: 'languageIsoCode',
            );
        }

        if ($templateIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $templateIds, 'a.template_id', 'templateId');
        }

        if ($mediaIds) {
            $allKeys = [];
            foreach (array_values($mediaIds) as $key => $value) {
                $currentKey = ':collectionMediaId' . $key;
                $allKeys[] = $currentKey;
                $params[$currentKey] = $value;
            }

            $where .= ' AND a.id IN (
                SELECT co.album_id
                FROM ' . self::COLLECTION_TABLE . ' AS co
                    INNER JOIN ' . self::COLLECTION_MEDIA_TABLE . ' AS cm ON cm.collection_id = co.id
                WHERE cm.media_id IN (' . implode(', ', $allKeys) . ')
            )';
        }

        if (isset($slug)) {
            $where .= ' AND als.slug = :albumSlug';
            $params[':albumSlug'] = $slug;
        }

        if ($searchQuery) {
            $searchQuery = StringUtil::cleanSearchQuery($searchQuery);

            $where .= " AND (MATCH(a.title_html) AGAINST('$searchQuery') OR a.title_html LIKE '%$searchQuery%')";
            $fields[] = "IF (a.title_html LIKE '%$searchQuery%', 1, 0) AS title_contains";
            $fields[] = "IF (a.title_html LIKE '$searchQuery%', 1, 0) AS begins_with";
            $fields[] = "IF (a.title_html LIKE '% $searchQuery%', 1, 0) AS word_begins_with";
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = Album::fromArray(
                data: $item,
                collections: $includeCollections
                    ? $this->filterCollectionBy(
                        albumIds: [$item['album_id']],
                        includeMedia: $includeMedia,
                        queryOptions: new QueryOptions(
                            orderBy: [
                                new QueryOrderBy('sequence', QueryOrderDirection::ASC),
                                new QueryOrderBy('id', QueryOrderDirection::ASC),
                            ],
                        ),
                    )
                    : [],
            );
        }

        return $output;
    }

    public function filterCollectionBy(
        array $collectionIds = [],
        array $albumIds = [],
        array $mediaIds = [],
        array $containMediaIds = [],
        ?string $searchQuery = null,
        bool $includeMedia = false,
        ?QueryOptions $queryOptions = null,
    ): array {
        if (!isset($queryOptions)) {
            $queryOptions = new QueryOptions();
        }

        $orderByMapping = [
            'sequence' => 'co.sequence',
            'id' => 'co.id',
            'title' => 'co.title_html',
            'begins_with' => 'begins_with',
            'word_begins_with' => 'word_begins_with',
            'title_contains' => 'title_contains',
        ];

        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            'co.id AS collection_id',
            'co.album_id AS collection_album_id',
            'co.main_media_id AS collection_main_media_id',
            'co.created_at AS collection_created_at',
            'co.updated_at AS collection_updated_at',
            'co.title_html AS collection_title_html',
            'co.subtitle_html AS collection_subtitle_html',
            'co.content_html AS collection_content_html',
            'co.`sequence` AS collection_sequence',

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
        ];

        $joins = ' FROM ' . self::COLLECTION_TABLE . ' AS co';
        $joins .= ' LEFT JOIN ' . MediaDataLayer::MEDIA_TABLE . ' AS m ON m.id = co.main_media_id';
        $joins .= ' LEFT JOIN ' . MediaDataLayer::MEDIA_EXIF_TABLE . ' AS me ON me.media_id = m.id';

        $where = ' WHERE 1';

        if ($collectionIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $collectionIds, 'co.id', 'collectionId');
        }

        if ($albumIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $albumIds, 'co.album_id', 'albumId');
        }

        if ($mediaIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $mediaIds, 'co.main_media_id', 'mainMediaId');
        }

        if ($containMediaIds) {
            $where .= ' AND co.id IN (SELECT collection_id FROM ' . self::COLLECTION_MEDIA_TABLE . ' WHERE 1 '
                . $this->generateWhereSqlCodeForIds($params, $containMediaIds, 'media_id', 'containMediaId') . ')';
        }

        if ($searchQuery) {
            $searchQuery = StringUtil::cleanSearchQuery($searchQuery);

            $where .= " AND (MATCH(co.title_html) AGAINST('$searchQuery') OR co.title_html LIKE '%$searchQuery%')";
            $fields[] = "IF (co.title_html LIKE '%$searchQuery%', 1, 0) AS title_contains";
            $fields[] = "IF (co.title_html LIKE '$searchQuery%', 1, 0) AS begins_with";
            $fields[] = "IF (co.title_html LIKE '% $searchQuery%', 1, 0) AS word_begins_with";
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = Collection::fromArray(
                data: $item,
                media: $includeMedia
                    ? $this->filterCollectionMediaBy(
                        collectionIds: [$item['collection_id']],
                        queryOptions: new QueryOptions(
                            orderBy: [
                                new QueryOrderBy('sequence', QueryOrderDirection::ASC),
                                new QueryOrderBy('id', QueryOrderDirection::ASC),
                            ],
                        ),
                    )
                    : [],
            );
        }

        return $output;
    }

    public function filterCollectionMediaBy(
        array $collectionMediaIds = [],
        array $collectionIds = [],
        array $mediaIds = [],
        ?QueryOptions $queryOptions = null,
    ): array {
        if (!isset($queryOptions)) {
            $queryOptions = new QueryOptions();
        }

        $orderByMapping = [
            'sequence' => 'cm.`sequence`',
            'id' => 'cm.id',
            'collection_id' => 'cm.collection_id',
        ];

        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            'cm.id AS collection_media_id',
            'cm.collection_id AS collection_media_collection_id',
            'cm.media_id AS collection_media_media_id',
            'cm.created_at AS collection_media_created_at',
            'cm.updated_at AS collection_media_updated_at',
            'cm.caption_html AS collection_media_caption_html',
            'cm.`sequence` AS collection_media_sequence',

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
        ];

        $joins = ' FROM ' . self::COLLECTION_MEDIA_TABLE . ' AS cm';
        $joins .= ' INNER JOIN ' . MediaDataLayer::MEDIA_TABLE . ' AS m ON m.id = cm.media_id';
        $joins .= ' LEFT JOIN ' . MediaDataLayer::MEDIA_EXIF_TABLE . ' AS me ON me.media_id = m.id';

        $where = ' WHERE 1';

        if ($collectionMediaIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $collectionMediaIds, 'cm.id', 'collectionMediaId');
        }

        if ($collectionIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $collectionIds, 'cm.collection_id', 'collectionId');
        }

        if ($mediaIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $mediaIds, 'cm.media_id', 'mediaId');
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = CollectionMedia::fromArray($item);
        }

        return $output;
    }

    public function filterAlbumSlugBy(
        array $albumIds = [],
        ?string $slug = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        if (!isset($queryOptions)) {
            $queryOptions = new QueryOptions();
        }

        $orderByMapping = [
            'id' => 'als.id',
            'created_at' => 'als.created_at',
        ];

        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            'als.id AS album_slug_id',
            'als.album_id AS album_slug_album_id',
            'als.created_at AS album_slug_created_at',
            'als.slug AS album_slug_slug',
        ];

        $joins = ' FROM ' . self::ALBUM_SLUG_TABLE . ' AS `als`';
        $where = ' WHERE 1';

        if ($albumIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $albumIds, 'als.album_id', 'albumId');
        }

        if ($slug) {
            $where .= ' AND als.slug = :albumSlug';
            $params[':albumSlug'] = $slug;
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = AlbumSlug::fromArray($item);
        }

        return $output;
    }

    public function storeAlbum(Album $item): Album
    {
        $res = $this->db->insert(
            tableName: self::ALBUM_TABLE,
            data: $item->asArray(),
        );

        $item->id = $res;

        return $item;
    }

    public function updateAlbum(Album $item): bool
    {
        return $this->db->update(
            tableName: self::ALBUM_TABLE,
            id: $item->id,
            data: $item->asArray(),
        );
    }

    public function storeCollection(Collection $item): ?Collection
    {
        $newId = $this->db->insert(
            tableName: self::COLLECTION_TABLE,
            data: $item->asArray(),
        );

        if (!$newId) {
            $this->logger->logError('Error inserting collection');
            return null;
        }

        $item->id = $newId;

        return $item;
    }

    public function updateCollection(Collection $item): bool
    {
        return $this->db->update(
            tableName: self::COLLECTION_TABLE,
            id: $item->id,
            data: $item->asArray(),
        );
    }

    public function updateCollectionFields(
        int $collectionId,
        int $mainMediaId,
    ): bool {
        $params = [];
        $fields = [];

        $fields[] = 'main_media_id = :mainMediaId';
        $params[':mainMediaId'] = $mainMediaId;

        if (!$params) {
            return true;
        }

        $params[':collectionId'] = $collectionId;
        $params[':updatedAt'] = DateUtil::getCurrentDateForMySql();
        $fields[] = 'updated_at = :updatedAt';

        $sql = '
            UPDATE ' . self::COLLECTION_TABLE . '
            SET ' . implode(',', $fields) . '
            WHERE id = :collectionId
        ';

        return $this->db->execute($sql, $params);
    }

    public function storeCollectionMedia(CollectionMedia $item): ?CollectionMedia
    {
        $newId = $this->db->insert(
            tableName: self::COLLECTION_MEDIA_TABLE,
            data: $item->asArray(),
        );

        if (!$newId) {
            $this->logger->logError('Error inserting collection media');
            return null;
        }

        $item->id = $newId;

        return $item;
    }

    public function updateCollectionMedia(CollectionMedia $item): bool
    {
        return $this->db->update(
            tableName: self::COLLECTION_MEDIA_TABLE,
            id: $item->id,
            data: $item->asArray(),
        );
    }

    public function deleteMediaForCollection(int $collectionMediaId): bool
    {
        return $this->db->delete(
            tableName: self::COLLECTION_MEDIA_TABLE,
            where: ['id' => $collectionMediaId],
        );
    }

    public function storeAlbumSlug(AlbumSlug $item): AlbumSlug
    {
        $res = $this->db->insert(
            tableName: self::ALBUM_SLUG_TABLE,
            data: $item->asArray(),
        );

        $item->id = $res;

        return $item;
    }

    public function updateAlbumSlugRelation(int $slugId, int $albumId): bool
    {
        return $this->db->update(
            tableName: self::ALBUM_SLUG_TABLE,
            id: $slugId,
            data: [
                'album_id' => $albumId,
            ],
        );
    }

    public function updateAlbumFields(
        int $albumId,
        ?AlbumStatus $newStatus = null,
    ): bool {
        $params = [];
        $fields = [];

        if ($newStatus) {
            $fields[] = 'status_id = :newStatusId';
            $params[':newStatusId'] = $newStatus->value;
        }

        if (!$params) {
            return true;
        }

        $params[':albumId'] = $albumId;
        $params[':updatedAt'] = DateUtil::getCurrentDateForMySql();
        $fields[] = 'updated_at = :updatedAt';

        $sql = '
            UPDATE ' . self::ALBUM_TABLE . '
            SET ' . implode(',', $fields) . '
            WHERE id = :albumId
        ';

        return $this->db->execute($sql, $params);
    }

    public function updateMediaSequenceForCollection(
        CollectionMedia $collectionMediaFrom,
        CollectionMedia $collectionMediaTo,
    ): bool {
        $resTrans = $this->db->withTransaction(
            function () use ($collectionMediaFrom, $collectionMediaTo) {
                if ($collectionMediaFrom->sequence === $collectionMediaTo->sequence) {
                    return new Feedback(true);
                }

                if ($collectionMediaFrom->sequence > $collectionMediaTo->sequence) {
                    $sql = '
                        UPDATE ' . self::COLLECTION_MEDIA_TABLE . '
                            SET `sequence` = `sequence` + :countDelta
                        WHERE collection_id = :collectionId
                            AND `sequence` >= :sequenceTo
                            AND `sequence` < :sequenceFrom
                    ';

                    $params = [
                        ':countDelta' => 1,
                        ':sequenceTo' => $collectionMediaTo->sequence,
                        ':sequenceFrom' => $collectionMediaFrom->sequence,
                        ':collectionId' => $collectionMediaFrom->collectionId,
                    ];
                } else {
                    $sql = '
                        UPDATE ' . self::COLLECTION_MEDIA_TABLE . '
                            SET `sequence` = `sequence` + :countDelta
                        WHERE collection_id = :collectionId
                            AND `sequence` > :sequenceFrom
                            AND `sequence` <= :sequenceTo
                    ';

                    $params = [
                        ':countDelta' => -1,
                        ':sequenceFrom' => $collectionMediaFrom->sequence,
                        ':sequenceTo' => $collectionMediaTo->sequence,
                        ':collectionId' => $collectionMediaFrom->collectionId,
                    ];
                }

                $resAgain = $this->db->execute($sql, $params);

                if (empty($resAgain)) {
                    return new Feedback($resAgain);
                }

                $sql = '
                    UPDATE ' . self::COLLECTION_MEDIA_TABLE . '
                        SET `sequence` = :newSequence
                    WHERE id = :collectionMediaId
                ';

                $params = [
                    ':newSequence' => $collectionMediaTo->sequence,
                    ':collectionMediaId' => $collectionMediaFrom->id,
                ];

                $res = $this->db->execute($sql, $params);

                return new Feedback($res);
            }
        );

        return $resTrans->isSuccess;
    }

    public function updateCollectionSequenceForAlbum(
        Collection $collectionFrom,
        Collection $collectionTo,
    ): bool {
        if ($collectionFrom->sequence === $collectionTo->sequence) {
            return true;
        }

        if ($collectionFrom->sequence > $collectionTo->sequence) {
            $sql = '
                UPDATE ' . self::COLLECTION_TABLE . '
                    SET `sequence` = `sequence` + :countDelta
                WHERE album_id = :albumId
                    AND `sequence` >= :sequenceTo
                    AND `sequence` < :sequenceFrom
            ';

            $params = [
                ':countDelta' => 1,
                ':sequenceTo' => $collectionTo->sequence,
                ':sequenceFrom' => $collectionFrom->sequence,
                ':albumId' => $collectionFrom->albumId,
            ];
        } else {
            $sql = '
                UPDATE ' . self::COLLECTION_TABLE . '
                    SET `sequence` = `sequence` + :countDelta
                WHERE album_id = :albumId
                    AND `sequence` > :sequenceFrom
                    AND `sequence` <= :sequenceTo
            ';

            $params = [
                ':countDelta' => -1,
                ':sequenceFrom' => $collectionFrom->sequence,
                ':sequenceTo' => $collectionTo->sequence,
                ':albumId' => $collectionFrom->albumId,
            ];
        }

        return $this->db->execute($sql, $params);
    }

    public function updateCollectionSequenceWhenMediaIsDeletedForAlbum(
        CollectionMedia $collectionMediaDeleted,
    ): bool {
        $sql = '
            UPDATE ' . self::COLLECTION_MEDIA_TABLE . '
                SET `sequence` = `sequence` + :countDelta
            WHERE collection_id = :collectionId
                AND `sequence` > :sequenceFrom
        ';

        $params = [
            ':countDelta' => -1,
            ':sequenceFrom' => $collectionMediaDeleted->sequence,
            ':collectionId' => $collectionMediaDeleted->collectionId,
        ];

        return $this->db->execute($sql, $params);
    }

    public function getMaxCollectionSequence(int $albumId): ?int
    {
        $res = $this->db->fetchColumn(
            '
                SELECT MAX(`sequence`)
                FROM ' . self::COLLECTION_TABLE . '
                WHERE `album_id` = :albumId
            ',
            [
                ':albumId' => $albumId,
            ]
        );

        return isset($res) ? (int)$res : null;
    }

    public function getMaxCollectionMediaSequence(int $collectionId): int
    {
        return (int)$this->db->fetchColumn(
            '
                SELECT COALESCE(MAX(`sequence`), 0)
                FROM ' . self::COLLECTION_MEDIA_TABLE . '
                WHERE `collection_id` = :collectionId
            ',
            [
                ':collectionId' => $collectionId,
            ]
        );
    }

    public function getTotalAlbums(): int
    {
        return (int)$this->db->fetchColumn(
            '
                SELECT COUNT(*) AS total
                FROM ' . self::ALBUM_TABLE . ' AS a
                WHERE a.status_id IN (:published, :draft, :private, :unlisted);
            ',
            [
                ':published' => AlbumStatus::Published->value,
                ':draft' => AlbumStatus::Draft->value,
                ':private' => AlbumStatus::Private->value,
                ':unlisted' => AlbumStatus::Unlisted->value,
            ]
        );
    }

    public function getMediaCountForCollectionId(int $collectionId): int
    {
        return (int)$this->db->fetchColumn(
            '
                SELECT COUNT(*)
                FROM ' . self::COLLECTION_MEDIA_TABLE . '
                WHERE collection_id = :collectionId;
            ',
            [
                ':collectionId' => $collectionId,
            ]
        );
    }
}
