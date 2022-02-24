<?php

namespace Amora\Core\Module\Article\Datalayer;

use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\Logger;
use Amora\Core\Model\Util\QueryOptions;
use Amora\Core\Module\Article\Model\Image;
use Amora\Core\Module\DataLayerTrait;

class ImageDataLayer
{
    use DataLayerTrait;

    const IMAGE_TABLE_NAME = 'image';

    public function __construct(private MySqlDb $db, private Logger $logger)
    {}

    public function filterImagesBy(
        array $imageIds = [],
        array $userIds = [],
        bool $excludeDeleted = true,
        ?QueryOptions $queryOptions = null,
    ): array {
        if (!isset($queryOptions)) {
            $queryOptions = new QueryOptions();
        }

        $orderByMapping = [
            'id' => 'i.id',
        ];

        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            'i.id AS image_id',
            'i.user_id',
            'i.file_path_original',
            'i.file_path_big',
            'i.file_path_medium',
            'i.full_url_original',
            'i.full_url_big',
            'i.full_url_medium',
            'i.caption',
            'i.created_at AS image_created_at',
            'i.updated_at AS image_updated_at',
        ];

        $joins = ' FROM ' . self::IMAGE_TABLE_NAME . ' AS i';
        $where = ' WHERE 1';

        if ($excludeDeleted) {
            $where .= ' AND i.is_deleted = 0';
        }

        if ($imageIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $imageIds, 'i.id', 'imageId');
        }

        if ($userIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $userIds, 'i.user_id', 'userId');
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = Image::fromArray($item);
        }

        return $output;
    }

    public function getImageForId(int $id): ?Image
    {
        $res = $this->filterImagesBy(imageIds: [$id]);
        return empty($res[0]) ? null : $res[0];
    }

    public function storeImage(Image $image): Image {
        $resInsert = $this->db->insert(self::IMAGE_TABLE_NAME, $image->asArray());

        if (empty($resInsert)) {
            $this->logger->logError('Error inserting image');
        }

        $image->id = (int)$resInsert;

        return $image;
    }

    public function deleteImage(int $imageId): bool
    {
        return $this->db->execute(
            '
                UPDATE ' . self::IMAGE_TABLE_NAME .
            '   SET is_deleted = 1
                WHERE id = :imageId
            ',
            [
                ':imageId' => $imageId
            ],
        );
    }
}
