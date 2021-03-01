<?php

namespace Amora\Core\Module\Article\Datalayer;

use Amora\Core\Database\MySqlDb;
use Amora\Core\Logger;
use Amora\Core\Module\Article\Model\Image;

class ImageDataLayer
{
    const IMAGE_TABLE_NAME = 'image';

    public function __construct(private MySqlDb $db, private Logger $logger)
    {}

    public function getDb(): MySqlDb
    {
        return $this->db;
    }

    private function getImages(
        ?int $imageId = null,
        ?int $userId = null
    ): array {
        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            'i.id',
            'i.user_id',
            'i.file_path_original',
            'i.file_path_big',
            'i.file_path_medium',
            'i.full_url_original',
            'i.full_url_big',
            'i.full_url_medium',
            'i.caption',
            'i.created_at',
            'i.updated_at'
        ];

        $joins = ' FROM ' . self::IMAGE_TABLE_NAME . ' AS i';
        $where = ' WHERE 1';

        if (isset($imageId)) {
            $where .= ' AND i.id = :imageId';
            $params[':imageId'] = $imageId;
        }

        if (isset($userId)) {
            $where .= ' AND i.user_id = :userId';
            $params[':userId'] = $userId;
        }

        $orderBy = ' ORDER BY i.id ASC';

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderBy;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = Image::fromArray($item);
        }

        return $output;
    }

    public function getAllImages(): array
    {
        return $this->getImages();
    }

    public function getImageForId(int $id): ?Image
    {
        $res = $this->getImages($id);
        return empty($res[0]) ? null : $res[0];
    }

    public function getImagesForUserId(int $userId): array
    {
        return $this->getImages(null, $userId);
    }

    public function storeImage(Image $image): Image {
        $resInsert = $this->db->insert(self::IMAGE_TABLE_NAME, $image->asArray());

        if (empty($resInsert)) {
            $this->logger->logError('Error inserting image');
        }

        $image->setId((int)$resInsert);

        return $image;
    }

    public function deleteImage(int $imageId): bool
    {
        $dbRes = $this->db->withTransaction(function() use ($imageId) {
            $resDel = $this->db->delete(
                self::IMAGE_TABLE_NAME,
                [
                    'id' => $imageId
                ]
            );

            if (empty($resDel)) {
                return ['success' => false];
            }

            return ['success' => true];
        });

        return empty($dbRes['success']) ? false : true;
    }
}
