<?php

namespace Amora\Core\Module\Album\Datalayer;

use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Model\AlbumPath;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Module\Article\Datalayer\MediaDataLayer;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\DataLayerTrait;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\Logger;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\User\DataLayer\UserDataLayer;

class AlbumDataLayer
{
    use DataLayerTrait;

    const ALBUM_TABLE = 'core_album';
    const ALBUM_SECTION_TABLE = 'core_album_section';
    const ALBUM_MEDIA_TABLE = 'core_album_media';
    const ALBUM_PATH_TABLE = 'core_album_path';

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
        ?string $path = null,
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
            'a.path AS album_path',

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
        ];

        $joins = ' FROM ' . self::ALBUM_TABLE . ' AS a';
        $joins .= ' INNER JOIN ' . UserDataLayer::USER_TABLE . ' AS u ON u.id = a.user_id';
        $joins .= ' LEFT JOIN ' . MediaDataLayer::MEDIA_TABLE . ' AS m ON m.id = a.main_media_id';

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

        if (isset($path)) {
            $where .= ' AND a.path = :albumPath';
            $params[':albumPath'] = $path;
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

    public function filterAlbumPathBy(
        array $albumIds = [],
        ?string $path = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        if (!isset($queryOptions)) {
            $queryOptions = new QueryOptions();
        }

        $orderByMapping = [
            'created_at' => 'ap.created_at',
        ];

        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            'ap.id AS album_path_id',
            'ap.album_id AS album_path_album_id',
            'ap.created_at AS album_path_created_at',
            'ap.path AS album_path_path',
        ];

        $joins = ' FROM ' . self::ALBUM_PATH_TABLE . ' AS ap';
        $where = ' WHERE 1';

        if ($albumIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $albumIds, 'ap.album_id', 'albumId');
        }

        if ($path) {
            $where .= ' AND ap.path = :albumPath';
            $params[':albumPath'] = $path;
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = AlbumPath::fromArray($item);
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
