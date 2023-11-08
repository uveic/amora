<?php

namespace Amora\Core\Module\Album\Datalayer;

use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Model\AlbumSlug;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Module\Article\Datalayer\MediaDataLayer;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\DataLayerTrait;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Logger;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\User\DataLayer\UserDataLayer;

class AlbumDataLayer
{
    use DataLayerTrait;

    const ALBUM_TABLE = 'core_album';
    const ALBUM_SECTION_TABLE = 'core_album_section';
    const ALBUM_MEDIA_TABLE = 'core_album_media';
    const ALBUM_SLUG_TABLE = 'core_album_slug';

    const ALBUM_STATUS_TABLE = 'core_album_status';
    const ALBUM_TEMPLATE_TABLE = 'core_album_template';

    public function __construct(
        private readonly MySqlDb $db,
        private readonly Logger $logger,
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
        ?string $slug = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        if (!isset($queryOptions)) {
            $queryOptions = new QueryOptions();
        }

        $orderByMapping = [
            'updated_at' => 'a.updated_at',
            'published_at' => 'a.published_at',
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
            'm.path AS media_path',
            'm.filename_original AS media_filename_original',
            'm.filename_small AS media_filename_small',
            'm.filename_medium AS media_filename_medium',
            'm.filename_large AS media_filename_large',
            'm.caption_html AS media_caption_html',
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

        if (isset($slug)) {
            $where .= ' AND als.slug = :albumSlug';
            $params[':albumSlug'] = $slug;
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = Album::fromArray($item);
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
