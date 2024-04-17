<?php

namespace Amora\Core\Module\Album\Datalayer;

use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Model\AlbumSection;
use Amora\Core\Module\Album\Model\AlbumSectionMedia;
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

    const ALBUM_TABLE = 'core_album';
    const ALBUM_SECTION_TABLE = 'core_album_section';
    const ALBUM_SECTION_MEDIA_TABLE = 'core_album_section_media';
    const ALBUM_SLUG_TABLE = 'core_album_slug';

    const ALBUM_STATUS_TABLE = 'core_album_status';
    const ALBUM_TEMPLATE_TABLE = 'core_album_template';

    public function __construct(
        private readonly MySqlDb $db,
        private readonly Logger $logger,
        private readonly MediaDataLayer $mediaDataLayer,
    ) {}

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
        bool $includeSections = false,
        bool $includeMedia = false,
        ?QueryOptions $queryOptions = null,
    ): array {
        if (!isset($queryOptions)) {
            $queryOptions = new QueryOptions();
        }

        $orderByMapping = [
            'updated_at' => 'a.updated_at',
            'published_at' => 'a.published_at',
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
            'm.filename_original AS media_filename_original',
            'm.filename_extra_small AS media_filename_extra_small',
            'm.filename_small AS media_filename_small',
            'm.filename_medium AS media_filename_medium',
            'm.filename_large AS media_filename_large',
            'm.filename_extra_large AS media_filename_extra_large',
            'm.caption_html AS media_caption_html',
            'm.filename_source AS media_filename_source',
            'm.created_at AS media_created_at',
            'm.updated_at AS media_updated_at',

            'als.id AS album_slug_id',
            'als.album_id AS album_slug_album_id',
            'als.created_at AS album_slug_created_at',
            'als.slug AS album_slug_slug',
        ];

        $joins = ' FROM ' . self::ALBUM_TABLE . ' AS a';
        $joins .= ' INNER JOIN ' . UserDataLayer::USER_TABLE . ' AS u ON u.id = a.user_id';
        $joins .= ' INNER JOIN ' . MediaDataLayer::MEDIA_TABLE . ' AS m ON m.id = a.main_media_id';
        $joins .= ' INNER JOIN ' . self::ALBUM_SLUG_TABLE . ' AS als ON als.id = a.slug_id';

        $where = ' WHERE 1';

        if ($albumIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $albumIds, 'a.id', 'albumId');
        }

        if ($statusIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $statusIds, 'a.status_id', 'statusId');
        }

        if ($languageIsoCodes) {
            $where .= $this->generateWhereSqlCodeForIds($params, $languageIsoCodes, 'a.language_iso_code', 'languageIsoCode');
        }

        if ($templateIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $templateIds, 'a.template_id', 'templateId');
        }

        if ($mediaIds) {
            $allKeys = [];
            foreach (array_values($mediaIds) as $key => $value) {
                $currentKey = ':albumSectionMediaId' . $key;
                $allKeys[] = $currentKey;
                $params[$currentKey] = $value;
            }

            $where .= ' AND a.id IN (
                SELECT `as`.album_id
                FROM ' . self::ALBUM_SECTION_TABLE . ' AS `as`
                    INNER JOIN ' . self::ALBUM_SECTION_MEDIA_TABLE . ' AS asm ON asm.album_section_id = `as`.id
                WHERE asm.media_id IN (' . implode(', ', $allKeys) . ')
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
                sections: $includeSections
                    ? $this->filterAlbumSectionBy(
                        albumIds: [$item['album_id']],
                        includeMedia: $includeMedia,
                        queryOptions: new QueryOptions(
                            orderBy: [
                                new QueryOrderBy('order', QueryOrderDirection::ASC),
                                new QueryOrderBy('id', QueryOrderDirection::ASC),
                            ],
                        ),
                    )
                    : [],
            );
        }

        return $output;
    }

    public function filterAlbumSectionBy(
        array $albumSectionIds = [],
        array $albumIds = [],
        array $mediaIds = [],
        bool $includeMedia = false,
        ?QueryOptions $queryOptions = null,
    ): array {
        if (!isset($queryOptions)) {
            $queryOptions = new QueryOptions();
        }

        $orderByMapping = [
            'order' => '`as`.sequence',
            'id' => '`as`.id',
        ];

        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            '`as`.id AS album_section_id',
            '`as`.album_id AS album_section_album_id',
            '`as`.main_media_id AS album_section_main_media_id',
            '`as`.created_at AS album_section_created_at',
            '`as`.updated_at AS album_section_updated_at',
            '`as`.title_html AS album_section_title_html',
            '`as`.subtitle_html AS album_section_subtitle_html',
            '`as`.content_html AS album_section_content_html',
            '`as`.`sequence` AS album_section_sequence',

            'm.id AS media_id',
            'm.user_id AS media_user_id',
            'm.type_id AS media_type_id',
            'm.status_id AS media_status_id',
            'm.width_original AS media_width_original',
            'm.height_original AS media_height_original',
            'm.path AS media_path',
            'm.filename_original AS media_filename_original',
            'm.filename_extra_small AS media_filename_extra_small',
            'm.filename_small AS media_filename_small',
            'm.filename_medium AS media_filename_medium',
            'm.filename_large AS media_filename_large',
            'm.filename_extra_large AS media_filename_extra_large',
            'm.caption_html AS media_caption_html',
            'm.filename_source AS media_filename_source',
            'm.created_at AS media_created_at',
            'm.updated_at AS media_updated_at',
        ];

        $joins = ' FROM ' . self::ALBUM_SECTION_TABLE . ' AS `as`';
        $joins .= ' LEFT JOIN ' . MediaDataLayer::MEDIA_TABLE . ' AS m ON m.id = `as`.main_media_id';

        $where = ' WHERE 1';

        if ($albumSectionIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $albumSectionIds, '`as`.id', 'albumSectionId');
        }

        if ($albumIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $albumIds, '`as`.album_id', 'albumId');
        }

        if ($mediaIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $mediaIds, '`as`.main_media_id', 'mainMediaId');
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = AlbumSection::fromArray(
                data: $item,
                media: $includeMedia
                    ? $this->filterAlbumSectionMediaBy(
                        albumSectionIds: [$item['album_section_id']],
                        queryOptions: new QueryOptions(
                            orderBy: [
                                new QueryOrderBy('order', QueryOrderDirection::ASC),
                                new QueryOrderBy('id', QueryOrderDirection::ASC),
                            ],
                        ),
                    )
                    : [],
            );
        }

        return $output;
    }

    public function filterAlbumSectionMediaBy(
        array $albumSectionMediaIds = [],
        array $albumSectionIds = [],
        array $mediaIds = [],
        ?QueryOptions $queryOptions = null,
    ): array {
        if (!isset($queryOptions)) {
            $queryOptions = new QueryOptions();
        }

        $orderByMapping = [
            'order' => 'asm.`sequence`',
            'id' => 'asm.id',
        ];

        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            'asm.id AS album_section_media_id',
            'asm.album_section_id AS album_section_media_album_section_id',
            'asm.media_id AS album_section_media_media_id',
            'asm.created_at AS album_section_media_created_at',
            'asm.updated_at AS album_section_media_updated_at',
            'asm.caption_html AS album_section_media_caption_html',
            'asm.`sequence` AS album_section_media_sequence',

            'm.id AS media_id',
            'm.user_id AS media_user_id',
            'm.type_id AS media_type_id',
            'm.status_id AS media_status_id',
            'm.width_original AS media_width_original',
            'm.height_original AS media_height_original',
            'm.path AS media_path',
            'm.filename_original AS media_filename_original',
            'm.filename_extra_small AS media_filename_extra_small',
            'm.filename_small AS media_filename_small',
            'm.filename_medium AS media_filename_medium',
            'm.filename_large AS media_filename_large',
            'm.filename_extra_large AS media_filename_extra_large',
            'm.caption_html AS media_caption_html',
            'm.filename_source AS media_filename_source',
            'm.created_at AS media_created_at',
            'm.updated_at AS media_updated_at',
        ];

        $joins = ' FROM ' . self::ALBUM_SECTION_MEDIA_TABLE . ' AS `asm`';
        $joins .= ' INNER JOIN ' . MediaDataLayer::MEDIA_TABLE . ' AS m ON m.id = asm.media_id';

        $where = ' WHERE 1';

        if ($albumSectionMediaIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $albumSectionMediaIds, 'asm.id', 'albumSectionMediaId');
        }

        if ($albumSectionIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $albumSectionIds, 'asm.album_section_id', 'albumSectionId');
        }

        if ($mediaIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $mediaIds, 'asm.media_id', 'mediaId');
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = AlbumSectionMedia::fromArray($item);
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

    public function storeAlbumSection(AlbumSection $item): AlbumSection
    {
        $res = $this->db->insert(
            tableName: self::ALBUM_SECTION_TABLE,
            data: $item->asArray(),
        );

        $item->id = $res;

        return $item;
    }

    public function updateAlbumSection(AlbumSection $item): bool
    {
        return $this->db->update(
            tableName: self::ALBUM_SECTION_TABLE,
            id: $item->id,
            data: $item->asArray(),
        );
    }

    public function storeAlbumSectionMedia(AlbumSectionMedia $item): AlbumSectionMedia
    {
        $res = $this->db->insert(
            tableName: self::ALBUM_SECTION_MEDIA_TABLE,
            data: $item->asArray(),
        );

        $item->id = $res;

        return $item;
    }

    public function updateAlbumSectionMedia(AlbumSectionMedia $item): bool
    {
        return $this->db->update(
            tableName: self::ALBUM_SECTION_MEDIA_TABLE,
            id: $item->id,
            data: $item->asArray(),
        );
    }

    public function deleteMediaForAlbumSection(int $albumSectionMediaId): bool
    {
        return $this->db->delete(
            tableName: self::ALBUM_SECTION_MEDIA_TABLE,
            where: ['id' => $albumSectionMediaId],
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

    public function updateMediaSequenceForAlbumSection(
        AlbumSectionMedia $albumSectionMediaFrom,
        AlbumSectionMedia $albumSectionMediaTo,
    ): bool {
        $resTrans = $this->db->withTransaction(
            function () use($albumSectionMediaFrom, $albumSectionMediaTo) {
                if ($albumSectionMediaFrom->sequence === $albumSectionMediaTo->sequence) {
                    return new Feedback(true);
                }

                if ($albumSectionMediaFrom->sequence > $albumSectionMediaTo->sequence) {
                    $sql = '
                        UPDATE ' . self::ALBUM_SECTION_MEDIA_TABLE . '
                            SET `sequence` = `sequence` + :countDelta
                        WHERE album_section_id = :albumSectionId
                            AND `sequence` >= :sequenceTo
                            AND `sequence` < :sequenceFrom
                    ';

                    $params = [
                        ':countDelta' => 1,
                        ':sequenceTo' => $albumSectionMediaTo->sequence,
                        ':sequenceFrom' => $albumSectionMediaFrom->sequence,
                        ':albumSectionId' => $albumSectionMediaFrom->albumSectionId,
                    ];
                } else {
                    $sql = '
                        UPDATE ' . self::ALBUM_SECTION_MEDIA_TABLE . '
                            SET `sequence` = `sequence` + :countDelta
                        WHERE album_section_id = :albumSectionId
                            AND `sequence` > :sequenceFrom
                            AND `sequence` <= :sequenceTo
                    ';

                    $params = [
                        ':countDelta' => -1,
                        ':sequenceFrom' => $albumSectionMediaFrom->sequence,
                        ':sequenceTo' => $albumSectionMediaTo->sequence,
                        ':albumSectionId' => $albumSectionMediaFrom->albumSectionId,
                    ];
                }

                $resAgain = $this->db->execute($sql, $params);

                if (empty($resAgain)) {
                    return new Feedback($resAgain);
                }

                $sql = '
                    UPDATE ' . self::ALBUM_SECTION_MEDIA_TABLE . '
                        SET `sequence` = :newSequence
                    WHERE id = :albumSectionMediaId
                ';

                $params = [
                    ':newSequence' => $albumSectionMediaTo->sequence,
                    ':albumSectionMediaId' => $albumSectionMediaFrom->id,
                ];

                $res = $this->db->execute($sql, $params);

                return new Feedback($res);
            }
        );

        return $resTrans->isSuccess;
    }

    public function updateSectionSequenceForAlbum(
        AlbumSection $albumSectionFrom,
        AlbumSection $albumSectionTo,
    ): bool {
        if ($albumSectionFrom->sequence === $albumSectionTo->sequence) {
            return true;
        }

        if ($albumSectionFrom->sequence > $albumSectionTo->sequence) {
            $sql = '
                UPDATE ' . self::ALBUM_SECTION_TABLE . '
                    SET `sequence` = `sequence` + :countDelta
                WHERE album_id = :albumId
                    AND `sequence` >= :sequenceTo
                    AND `sequence` < :sequenceFrom
            ';

            $params = [
                ':countDelta' => 1,
                ':sequenceTo' => $albumSectionTo->sequence,
                ':sequenceFrom' => $albumSectionFrom->sequence,
                ':albumId' => $albumSectionFrom->albumId,
            ];
        } else {
            $sql = '
                UPDATE ' . self::ALBUM_SECTION_TABLE . '
                    SET `sequence` = `sequence` + :countDelta
                WHERE album_id = :albumId
                    AND `sequence` > :sequenceFrom
                    AND `sequence` <= :sequenceTo
            ';

            $params = [
                ':countDelta' => -1,
                ':sequenceFrom' => $albumSectionFrom->sequence,
                ':sequenceTo' => $albumSectionTo->sequence,
                ':albumId' => $albumSectionFrom->albumId,
            ];
        }

        return $this->db->execute($sql, $params);
    }

    public function updateSectionSequenceWhenMediaIsDeletedForAlbum(
        AlbumSectionMedia $albumSectionMediaDeleted,
    ): bool {
        $sql = '
            UPDATE ' . self::ALBUM_SECTION_MEDIA_TABLE . '
                SET `sequence` = `sequence` + :countDelta
            WHERE album_section_id = :albumSectionId
                AND `sequence` > :sequenceFrom
        ';

        $params = [
            ':countDelta' => -1,
            ':sequenceFrom' => $albumSectionMediaDeleted->sequence,
            ':albumSectionId' => $albumSectionMediaDeleted->albumSectionId,
        ];

        return $this->db->execute($sql, $params);
    }

    public function getMaxAlbumSectionSequence(int $albumId): ?int
    {
        $res = $this->db->fetchColumn(
            '
                SELECT MAX(`sequence`)
                FROM ' . self::ALBUM_SECTION_TABLE . '
                WHERE `album_id` = :albumId
            ',
            [
                ':albumId' => $albumId,
            ]
        );

        return isset($res) ? (int)$res : null;
    }

    public function getMaxAlbumSectionMediaSequence(int $albumSectionId): int
    {
        return (int)$this->db->fetchColumn(
            '
                SELECT COALESCE(MAX(`sequence`), 0)
                FROM ' . self::ALBUM_SECTION_MEDIA_TABLE . '
                WHERE `album_section_id` = :albumSectionId
            ',
            [
                ':albumSectionId' => $albumSectionId,
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
}
