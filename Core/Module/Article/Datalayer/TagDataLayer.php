<?php

namespace Amora\Core\Module\Article\Datalayer;

use Amora\Core\Database\Model\TransactionResponse;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Logger;
use Amora\Core\Module\Article\Model\Tag;
use Amora\Core\Module\DataLayerTrait;

class TagDataLayer
{
    use DataLayerTrait;

    const TAG_TABLE_NAME = 'tag';

    public function __construct(private MySqlDb $db, private Logger $logger)
    {}

    public function filterTagsBy(
        array $tagIds = [],
        array $articleIds = [],
        ?string $tagName = null,
    ): array {
        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            't.id AS tag_id',
            't.name',
        ];

        $joins = ' FROM ' . self::TAG_TABLE_NAME . ' AS t';
        $where = ' WHERE 1';

        if ($tagIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $tagIds, 't.id', 'tagId');
        }

        if (isset($tagName)) {
            $where .= $this->generateWhereSqlCodeForIds($params, [$tagName], 't.name', 'tagName');
        }

        if ($articleIds) {
            $joins .= ' LEFT JOIN ' . ArticleDataLayer::ARTICLE_TAG_RELATION_TABLE . ' AS at'
                . ' ON at.tag_id = t.id';
            $where .= $this->generateWhereSqlCodeForIds(
                params: $params,
                ids: $articleIds,
                dbColumnName: 'at.article_id',
                keyName: 'articleId',
            );
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

    public function getTagForId(int $id): ?Tag
    {
        $res = $this->filterTagsBy(tagIds: [$id]);
        return empty($res[0]) ? null : $res[0];
    }

    public function getTagForName(string $name): ?Tag
    {
        $res = $this->filterTagsBy(tagName: $name);
        return empty($res[0]) ? null : $res[0];
    }

    public function getTagsForArticleId(int $articleId): array
    {
        return $this->filterTagsBy(articleIds: [$articleId]);
    }

    public function storeTag(Tag $tag): Tag {
        $resInsert = $this->db->insert(self::TAG_TABLE_NAME, $tag->asArray());

        if (empty($resInsert)) {
            $this->logger->logError('Error inserting tag');
        }

        $tag->id = (int)$resInsert;

        return $tag;
    }

    public function destroyTag(int $id): bool
    {
        $dbRes = $this->db->withTransaction(function() use ($id) {
            $resAr = $this->db->delete(
                ArticleDataLayer::ARTICLE_TAG_RELATION_TABLE,
                ['tag_id' => $id]
            );

            if (empty($resAr)) {
                return new TransactionResponse(false);
            }

            $resDel = $this->db->delete(self::TAG_TABLE_NAME, ['id' => $id]);
            if (empty($resDel)) {
                return new TransactionResponse(false);
            }

            return new TransactionResponse(true);
        });

        return $dbRes->isSuccess();
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
