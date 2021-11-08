<?php

namespace Amora\Core\Module\Article\Datalayer;

use Amora\Core\Database\Model\TransactionResponse;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Module\DataLayerTrait;
use Amora\Core\Util\DateUtil;
use Throwable;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Logger;
use Amora\Core\Model\Util\QueryOptions;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\ArticleSection;
use Amora\Core\Module\User\Datalayer\UserDataLayer;
use Amora\Core\Module\Article\Value\ArticleStatus;

class ArticleDataLayer
{
    use DataLayerTrait;

    const ARTICLE_TABLE = 'article';
    const ARTICLE_HISTORY_TABLE = 'article_history';

    const ARTICLE_SECTION_TABLE = 'article_section';
    const ARTICLE_SECTION_IMAGE_TABLE = 'article_section_image';

    const ARTICLE_TAG_RELATION_TABLE = 'article_tag_relation';

    public function __construct(
        private MySqlDb $db,
        private Logger $logger,
        private ImageDataLayer $imageDataLayer,
        private TagDataLayer $tagDataLayer,
    ) {}

    public function getDb(): MySqlDb
    {
        return $this->db;
    }

    public function filterArticlesBy(
        array $articleIds = [],
        array $statusIds = [],
        array $typeIds = [],
        ?string $uri = null,
        array $tagIds = [],
        bool $includeTags = false,
        bool $includePublishedAtInTheFuture = false,
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
            'a.id AS article_id',
            'a.user_id',
            'a.status_id',
            'a.type_id',
            'a.created_at AS article_created_at',
            'a.updated_at AS article_updated_at',
            'a.published_at',
            'a.title',
            'a.content_html',
            'a.main_image_id',
            'a.uri',

            'u.language_id',
            'u.role_id',
            'u.journey_id',
            'u.created_at AS user_created_at',
            'u.updated_at AS user_updated_at',
            'u.email',
            'u.name',
            'u.password_hash',
            'u.bio',
            'u.is_enabled',
            'u.verified',
            'u.timezone',
            'u.change_email_to',

            'i.id AS image_id',
            'i.user_id AS image_user_id',
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

        $joins = ' FROM ' . self::ARTICLE_TABLE . ' AS a';
        $joins .= ' JOIN ' . UserDataLayer::USER_TABLE . ' AS u ON u.id = a.user_id';
        $joins .= ' LEFT JOIN ' . ImageDataLayer::IMAGE_TABLE_NAME
            . ' AS i ON i.id = a.main_image_id';

        $where = ' WHERE 1';

        if ($articleIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $articleIds, 'a.id', 'articleId');
        }

        if ($statusIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $statusIds, 'a.status_id', 'statusId');
        }

        if ($typeIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $typeIds, 'a.type_id', 'typeId');
        }

        if (isset($uri)) {
            $where .= ' AND a.uri = :articleUri';
            $params[':articleUri'] = $uri;
        }

        if ($tagIds) {
            $joins .= ' JOIN ' . ArticleDataLayer::ARTICLE_TAG_RELATION_TABLE
                . ' AS at ON at.article_id = a.id';

            $where .= $this->generateWhereSqlCodeForIds($params, $tagIds, 'at.tag_id', 'tagId');
        }

        if (!$includePublishedAtInTheFuture) {
            $where .= ' AND a.published_at <= :publishedAt';
            $params[':publishedAt'] = DateUtil::getCurrentDateForMySql();
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            if ($includeTags) {
                $item['tags'] = $this->tagDataLayer->getTagsForArticleId($item['article_id']);
            }
            $output[] = Article::fromArray($item);
        }

        return $output;
    }

    public function getArticleForId(int $id, bool $includeTags = false): ?Article
    {
        $res = $this->filterArticlesBy(
            articleIds: [$id],
            includeTags: $includeTags,
            includePublishedAtInTheFuture: true,
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getArticlesForTypeIds(array $articleTypeIds): array
    {
        return $this->filterArticlesBy(typeIds: $articleTypeIds);
    }

    public function getArticleForUri(string $uri): ?Article
    {
        $res = $this->filterArticlesBy(uri: $uri);
        return empty($res[0]) ? null : $res[0];
    }

    public function updateArticle(Article $article): bool
    {
        $articleArray = $article->asArray();
        unset($articleArray['created_at']);
        unset($articleArray['user_id']); // Keep the user ID that originally created it
        $articleId = $article->getId();
        $resUpdate = $this->db->update(self::ARTICLE_TABLE, $articleId, $articleArray);

        if (empty($resUpdate)) {
            $this->logger->logError('Error updating article. Article ID: ' . $articleId);
            return false;
        }

        return true;
    }

    public function createNewArticle(
        Article $article,
        ?string $userIp,
        ?string $userAgent
    ): ?Article {

        $resInsert = $this->db->insert(self::ARTICLE_TABLE, $article->asArray());

        if (empty($resInsert)) {
            $this->logger->logError('Error inserting article');
            return null;
        }

        $article->setId((int)$resInsert);

        $resHistory = $this->insertArticleHistory($article, $userIp, $userAgent);
        if (empty($resHistory)) {
            $this->logger->logError('Error inserting article history');
            return null;
        }

        return $article;
    }

    public function insertArticleHistory(Article $article, ?string $userIp, ?string $userAgent): bool
    {
        $data = [
            'article_id' => $article->getId(),
            'user_id' => $article->getUser()->getId(),
            'status_id' => $article->getStatusId(),
            'type_id' => $article->getTypeId(),
            'created_at' => $article->getCreatedAt(),
            'title' => $article->getTitle(),
            'content_html' => $article->getContentHtml(),
            'main_image_id' => $article->getMainImageId(),
            'uri' => $article->getUri(),
            'ip' => $userIp,
            'user_agent' => $userAgent
        ];
        $resInsert = $this->db->insert(self::ARTICLE_HISTORY_TABLE, $data);

        if (empty($resInsert)) {
            $this->logger->logError('Error inserting article history');
            return false;
        }

        return true;
    }

    public function deleteArticle(Article $article, ?string $userIp, ?string $userAgent): bool
    {
        $resTransaction = $this->db->withTransaction(
            function () use ($article, $userIp, $userAgent) {
                $resHistory = $this->insertArticleHistory($article, $userIp, $userAgent);
                if (empty($resHistory)) {
                    return new TransactionResponse(false);
                }

                $resDe = $this->db->update(
                    self::ARTICLE_TABLE,
                    $article->getId(),
                    [
                        'status_id' => ArticleStatus::DELETED
                    ]
                );

                if (empty($resDe)) {
                    return new TransactionResponse(false);
                }

                return new TransactionResponse(true);
            }
        );

        return $resTransaction->isSuccess();
    }

    private function getArticleSections(
        ?int $articleSectionId = null,
        ?int $articleId = null,
        ?int $sectionTypeId = null,
    ): array {
        $params = [];
        $sql = '
            SELECT
                aSec.id,
                aSec.article_id,
                aSec.article_section_type_id,
                aSec.content_html,
                aSec.order,
                aSecI.image_id,
                aSecI.caption AS image_caption,
                aSec.created_at,
                aSec.updated_at
            FROM ' . self::ARTICLE_SECTION_TABLE . ' AS aSec
                LEFT JOIN ' . self::ARTICLE_SECTION_IMAGE_TABLE . ' AS aSecI
                    ON aSec.id = aSecI.article_section_id
            WHERE 1
        ';

        if (isset($articleSectionId)) {
            $sql .= ' AND aSec.id = :articleSectionId';
            $params[':articleSectionId'] = $articleSectionId;
        }

        if (isset($articleId)) {
            $sql .= ' AND aSec.article_id = :articleId';
            $params[':articleId'] = $articleId;
        }

        if (isset($sectionTypeId)) {
            $sql .= ' AND aSec.article_section_type_id = :articleSectionTypeId';
            $params[':articleSectionTypeId'] = $sectionTypeId;
        }

        $sql .= ' ORDER BY aSec.`order` ASC, aSec.id DESC';

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = ArticleSection::fromArray($item);
        }

        return $output;
    }

    public function getSectionsForArticleId(int $articleId): array
    {
        return $this->getArticleSections(null, $articleId);
    }

    public function createArticleSection(ArticleSection $articleSection): ?ArticleSection
    {
        $resInsert = $this->db->insert(
            self::ARTICLE_SECTION_TABLE, $articleSection->asArray()
        );

        if (empty($resInsert)) {
            $this->logger->logError('Error inserting article');
            return null;
        }

        $articleSection->setId((int)$resInsert);

        return $articleSection;
    }

    public function updateArticleSection(ArticleSection $articleSection): bool
    {
        $array = $articleSection->asArray();
        unset($array['created_at']);
        $resUpdate = $this->db->update(
            self::ARTICLE_SECTION_TABLE,
            $articleSection->getId(),
            $array
        );

        if (empty($resUpdate)) {
            $this->logger->logError(
                'Error updating article section. Article Section ID: ' . $articleSection->getId()
            );
            return false;
        }

        return true;
    }

    public function deleteArticleSection(int $articleSectionId): bool
    {
        return $this->db->delete(self::ARTICLE_SECTION_TABLE, ['id' => $articleSectionId]);
    }

    public function createArticleSectionImage(
        int $articleSectionId,
        int $imageId,
        ?string $imageCaption
    ): bool {
        try {
            $this->db->insert(
                ArticleDataLayer::ARTICLE_SECTION_IMAGE_TABLE,
                [
                    'image_id' => $imageId,
                    'article_section_id' => $articleSectionId,
                    'caption' => $imageCaption
                ]
            );
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error inserting entry into ' . ArticleDataLayer::ARTICLE_SECTION_IMAGE_TABLE .
                ' - ImageId: ' . $imageId .
                ' - ArticleSectionId: ' . $articleSectionId .
                ' - Error message: ' . $t->getMessage()
            );
            return false;
        }

        return true;
    }

    public function updateArticleSectionImage(
        int $articleSectionId,
        int $imageId,
        ?string $imageCaption
    ): bool {
        try {
            $this->db->execute(
                'UPDATE ' . ArticleDataLayer::ARTICLE_SECTION_IMAGE_TABLE .
                ' SET caption = :caption' .
                ' WHERE image_id = :imageId
                    AND article_section_id = :articleSectionId',
                [
                    ':imageId' => $imageId,
                    ':articleSectionId' => $articleSectionId,
                    ':caption' => $imageCaption
                ]
            );
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error updating entry into ' . ArticleDataLayer::ARTICLE_SECTION_IMAGE_TABLE .
                ' - ImageId: ' . $imageId .
                ' - ArticleSectionId: ' . $articleSectionId .
                ' - Error message: ' . $t->getMessage()
            );
            return false;
        }

        return true;
    }

    public function deleteArticleSectionImage(int $articleSectionId, int $imageId): bool
    {
        return $this->db->execute(
            'DELETE FROM ' . ArticleDataLayer::ARTICLE_SECTION_IMAGE_TABLE .
            ' WHERE image_id = :imageId
                AND article_section_id = :articleSectionId',
            [
                ':imageId' => $imageId,
                ':articleSectionId' => $articleSectionId
            ]
        );
    }
}
