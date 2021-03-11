<?php

namespace Amora\Core\Module\Article\Datalayer;

use Amora\Core\Database\MySqlDb;
use Amora\Core\Logger;
use Amora\Core\Module\Article\Model\Tag;

class TagDataLayer
{
    const TAG_TABLE_NAME = 'tag';

    public function __construct(private MySqlDb $db, private Logger $logger)
    {}

    public function getDb(): MySqlDb
    {
        return $this->db;
    }

    private function getTags(
        ?int $tagId = null,
        ?int $articleId = null,
        ?string $tagName = null,
    ): array {
        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            't.id',
            't.name',
        ];

        $joins = ' FROM ' . self::TAG_TABLE_NAME . ' AS t';
        $where = ' WHERE 1';

        if (isset($tagId)) {
            $where .= ' AND t.id = :tagId';
            $params[':tagId'] = $tagId;
        }

        if (isset($tagName)) {
            $where .= ' AND t.name = :tagName';
            $params[':tagName'] = $tagName;
        }

        if (isset($articleId)) {
            $joins .= ' LEFT JOIN ' . ArticleDataLayer::ARTICLE_TAG_RELATION_TABLE . ' AS at'
                . ' ON at.tag_id = t.id';
            $where .= ' AND at.article_id = :articleId';
            $params[':articleId'] = $articleId;
        }

        $orderBy = ' ORDER BY t.id ASC';

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderBy;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = Tag::fromArray($item);
        }

        return $output;
    }

    public function getAllTags(): array
    {
        return $this->getTags();
    }

    public function getTagForId(int $id): ?Tag
    {
        $res = $this->getTags($id);
        return empty($res[0]) ? null : $res[0];
    }

    public function getTagForName(string $name): ?Tag
    {
        $res = $this->getTags(null, null, $name);
        return empty($res[0]) ? null : $res[0];
    }

    public function getTagsForArticleId(int $articleId): array
    {
        return $this->getTags(null, $articleId);
    }

    public function storeTag(Tag $tag): Tag {
        $resInsert = $this->db->insert(self::TAG_TABLE_NAME, $tag->asArray());

        if (empty($resInsert)) {
            $this->logger->logError('Error inserting tag');
        }

        $tag->setId((int)$resInsert);

        return $tag;
    }

    public function destroyTag(int $id): bool
    {
        $dbRes = $this->db->withTransaction(function() use ($id) {
            $resAr = $this->db->delete(
                ArticleDataLayer::ARTICLE_TAG_RELATION_TABLE,
                [
                    'tag_id' => $id
                ]
            );

            if (empty($resAr)) {
                return ['success' => false];
            }

            $resDel = $this->db->delete(
                self::TAG_TABLE_NAME,
                [
                    'id' => $id
                ]
            );

            if (empty($resDel)) {
                return ['success' => false];
            }

            return ['success' => true];
        });

        return empty($dbRes['success']) ? false : true;
    }

    public function insertArticleTagRelation(int $tagId, int $articleId): bool
    {
        $this->db->insert(
            ArticleDataLayer::ARTICLE_TAG_RELATION_TABLE,
            [
                'tag_id' => $tagId,
                'article_id' => $articleId,
            ]
        );

        return true;
    }

    public function deleteArticleTagRelation(int $tagId, int $articleId): bool
    {
        return $this->db->delete(
            ArticleDataLayer::ARTICLE_TAG_RELATION_TABLE,
            [
                'tag_id' => $tagId,
                'article_id' => $articleId
            ]
        );
    }
}
