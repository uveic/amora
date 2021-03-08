<?php

namespace Amora\Core\Module\Article\Datalayer;

use Amora\Core\Module\Article\Value\ArticleType;
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
    const ARTICLE_TABLE_NAME = 'article';
    const ARTICLE_HISTORY_TABLE_NAME = 'article_history';

    const ARTICLE_SECTION_TABLE_NAME = 'article_section';
    const ARTICLE_SECTION_IMAGE_TABLE_NAME = 'article_section_image';

    const ARTICLE_TAG_RELATION_TABLE_NAME = 'article_tag_relation';

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

    private function getArticles(
        ?int $articleId = null,
        ?int $statusId = null,
        array $typeIds = [],
        ?string $uri = null,
        array $tagIds = [],
        bool $includeTags = false,
        ?QueryOptions $queryOptions = null,
    ): array {
        if (!isset($queryOptions)) {
            $queryOptions = new QueryOptions();
        }

        $orderByMapping = [
            'published_at' => 'a.published_at',
            'updated_at' => 'a.updated_at',
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

        $joins = ' FROM ' . self::ARTICLE_TABLE_NAME . ' AS a';
        $joins .= ' JOIN ' . UserDataLayer::USER_TABLE . ' AS u ON u.id = a.user_id';
        $joins .= ' LEFT JOIN ' . ImageDataLayer::IMAGE_TABLE_NAME
            . ' AS i ON i.id = a.main_image_id';

        $where = ' WHERE 1';

        if (isset($articleId)) {
            $where .= ' AND a.id = :articleId';
            $params[':articleId'] = $articleId;
        }

        if (isset($statusId)) {
            $where .= ' AND a.status_id = :statusId';
            $params[':statusId'] = $statusId;
        }

        if ($typeIds) {
            $allKeys = [];
            foreach (array_values($typeIds) as $key => $typeId) {
                $currentKey = ':typeId' . $key;
                $allKeys[] = $currentKey;
                $params[$currentKey] = $typeId;
            }
            $where .= ' AND a.type_id IN (' . implode(', ', $allKeys) . ')';
        }

        if (isset($uri)) {
            $where .= ' AND a.uri = :articleUri';
            $params[':articleUri'] = $uri;
        }

        if ($tagIds) {
            $allKeys = [];
            $joins .= ' JOIN ' . ArticleDataLayer::ARTICLE_TAG_RELATION_TABLE_NAME
                . ' AS at ON at.article_id = a.id';
            foreach (array_values($tagIds) as $key => $tagId) {
                $currentKey = ':tagId' . $key;
                $allKeys[] = $currentKey;
                $params[$currentKey] = $tagId;
            }
            $where .= ' AND at.tag_id IN (' . implode(', ', $allKeys) . ')';
        }

        $orderBy = ' ORDER BY ' .
            (empty($orderByMapping[$queryOptions->getOrderBy()])
                ? 'a.updated_at'
                : $orderByMapping[$queryOptions->getOrderBy()])
            . ' ' . $queryOptions->getSortingDirection();

        $limit = ' LIMIT ' . $queryOptions->getLimit() . ' OFFSET ' . $queryOptions->getOffset();

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderBy . $limit;

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
        $res = $this->getArticles(
            articleId: $id,
            includeTags: $includeTags
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getArticlesForTypeIds(array $articleTypeIds): array
    {
        return $this->getArticles(typeIds: $articleTypeIds);
    }

    public function getArticleForUri(string $uri): ?Article
    {
        $res = $this->getArticles(uri: $uri);
        return empty($res[0]) ? null : $res[0];
    }

    public function getHomepageArticle(): ?Article
    {
        $res = $this->getArticles(
            statusId: ArticleStatus::PUBLISHED,
            typeIds: [ArticleType::HOMEPAGE],
        );

        if (count($res) > 1) {
            $this->logger->logError(
                'There are more than one homepage article. Returning the most recently updated.'
            );
        }

        return empty($res[0]) ? null : $res[0];
    }

    public function filterArticlesBy(
        array $tagIds = [],
        int $statusId = null,
        array $typeIds = [],
        bool $includeTags = false,
        ?QueryOptions $queryOptions = null
    ): array
    {
        return $this->getArticles(
            statusId: $statusId,
            typeIds: $typeIds,
            tagIds: $tagIds,
            includeTags: $includeTags,
            queryOptions: $queryOptions
        );
    }

    public function updateArticle(Article $article): bool
    {
        $articleArray = $article->asArray();
        unset($articleArray['created_at']);
        unset($articleArray['user_id']); // Keep the user ID that originally created it
        $articleId = $article->getId();
        $resUpdate = $this->db->update(self::ARTICLE_TABLE_NAME, $articleId, $articleArray);

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

        $resInsert = $this->db->insert(self::ARTICLE_TABLE_NAME, $article->asArray());

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
        $resInsert = $this->db->insert(self::ARTICLE_HISTORY_TABLE_NAME, $data);

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
                    return [
                        'success' => false
                    ];
                }

                $resDe = $this->db->update(
                    self::ARTICLE_TABLE_NAME,
                    $article->getId(),
                    [
                        'status_id' => ArticleStatus::DELETED
                    ]
                );

                if (empty($resDe)) {
                    return [
                        'success' => false
                    ];
                }

                return [
                    'success' => true
                ];
            }
        );

        return empty($resTransaction['success']) ? false : true;
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
            FROM ' . self::ARTICLE_SECTION_TABLE_NAME . ' AS aSec
                LEFT JOIN ' . self::ARTICLE_SECTION_IMAGE_TABLE_NAME . ' AS aSecI
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
            self::ARTICLE_SECTION_TABLE_NAME, $articleSection->asArray()
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
            self::ARTICLE_SECTION_TABLE_NAME,
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
        return $this->db->delete(self::ARTICLE_SECTION_TABLE_NAME, ['id' => $articleSectionId]);
    }

    public function createArticleSectionImage(
        int $articleSectionId,
        int $imageId,
        ?string $imageCaption
    ): bool {
        try {
            $this->db->insert(
                ArticleDataLayer::ARTICLE_SECTION_IMAGE_TABLE_NAME,
                [
                    'image_id' => $imageId,
                    'article_section_id' => $articleSectionId,
                    'caption' => $imageCaption
                ]
            );
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error inserting entry into ' . ArticleDataLayer::ARTICLE_SECTION_IMAGE_TABLE_NAME .
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
                'UPDATE ' . ArticleDataLayer::ARTICLE_SECTION_IMAGE_TABLE_NAME .
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
                'Error updating entry into ' . ArticleDataLayer::ARTICLE_SECTION_IMAGE_TABLE_NAME .
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
            'DELETE FROM ' . ArticleDataLayer::ARTICLE_SECTION_IMAGE_TABLE_NAME .
            ' WHERE image_id = :imageId
                AND article_section_id = :articleSectionId',
            [
                ':imageId' => $imageId,
                ':articleSectionId' => $articleSectionId
            ]
        );
    }
}
