<?php

namespace uve\core\module\article\datalayer;

use Throwable;
use uve\core\database\MySqlDb;
use uve\core\Logger;
use uve\core\module\article\model\Image;

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
        ?int $articleId = null,
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

        if (isset($articleId)) {
            $joins .= ' LEFT JOIN ' . ArticleDataLayer::ARTICLE_IMAGE_RELATION_TABLE_NAME . ' AS ar'
                . ' ON ar.image_id = i.id';
            $where .= ' AND ar.article_id = :articleId';
            $params[':articleId'] = $articleId;
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

    public function getImagesForArticleId(int $articleId): array
    {
        return $this->getImages(null, $articleId);
    }

    public function getImagesForUserId(int $userId): array
    {
        return $this->getImages(null, null, $userId);
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
            $resAr = $this->db->delete(
                ArticleDataLayer::ARTICLE_IMAGE_RELATION_TABLE_NAME,
                [
                    'image_id' => $imageId
                ]
            );

            if (empty($resAr)) {
                return ['success' => false];
            }

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

    public function insertArticleImageRelation(
        int $imageId,
        int $articleId,
        ?int $order = null
    ): bool {
        try {
            $this->db->insert(
                ArticleDataLayer::ARTICLE_IMAGE_RELATION_TABLE_NAME,
                [
                    'image_id' => $imageId,
                    'article_id' => $articleId,
                    'order' => $order
                ]
            );
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error inserting entry into ' . ArticleDataLayer::ARTICLE_IMAGE_RELATION_TABLE_NAME .
                ' - ImageId: ' . $imageId .
                ' - ArticleId: ' . $articleId .
                ' - Error message: ' . $t->getMessage()
            );
            return false;
        }

        return true;
    }
}
