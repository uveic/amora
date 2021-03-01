<?php

namespace Amora\Core\Module\Article\Datalayer;

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
        ?int $typeId = null,
        ?string $uri = null,
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

        $orderBy = empty($orderByMapping[$queryOptions->getOrderBy()])
            ? 'a.updated_at'
            : $orderByMapping[$queryOptions->getOrderBy()];

        $params = [];
        $sql = '
            SELECT
                a.id AS article_id,
                a.user_id,
                a.status_id,
                a.type_id,
                a.created_at AS article_created_at,
                a.updated_at AS article_updated_at,
                a.published_at,
                a.title,
                a.content_html,
                a.main_image_src,
                a.uri,
                u.language_id,
                u.role_id,
                u.journey_id,
                u.created_at AS user_created_at,
                u.updated_at AS user_updated_at,
                u.email,
                u.name,
                u.password_hash,
                u.bio,
                u.is_enabled,
                u.verified,
                u.timezone,
                u.previous_email_address
            FROM ' . self::ARTICLE_TABLE_NAME . ' AS a
                JOIN ' . UserDataLayer::USER_TABLE_NAME . ' AS u
                    ON u.id = a.user_id
            WHERE 1
        ';

        if (isset($articleId)) {
            $sql .= ' AND a.id = :article_id';
            $params[':article_id'] = $articleId;
        }

        if (isset($statusId)) {
            $sql .= ' AND a.status_id = :status_id';
            $params[':status_id'] = $statusId;
        }

        if (isset($typeId)) {
            $sql .= ' AND a.type_id = :type_id';
            $params[':type_id'] = $typeId;
        }

        if (isset($uri)) {
            $sql .= ' AND a.uri = :uri';
            $params[':uri'] = $uri;
        }

        $sql .= ' ORDER BY ' . $orderBy . ' ' . $queryOptions->getSortingDirection();
        $sql .= ' LIMIT ' . $queryOptions->getLimit() . ' OFFSET ' . $queryOptions->getOffset();

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

    public function getAllArticles(?QueryOptions $queryOptions): array
    {
        return $this->getArticles(null, null, null, null, false, $queryOptions);
    }

    public function getArticleForId(int $id, $includeTags = false): ?Article
    {
        $res = $this->getArticles($id, null, null, null, $includeTags);
        return empty($res[0]) ? null : $res[0];
    }

    public function getArticleForUri(string $uri): ?Article
    {
        $res = $this->getArticles(null, null, null, $uri);
        return empty($res[0]) ? null : $res[0];
    }

    public function filterArticlesBy(
        int $statusId = null,
        int $typeId = null,
        ?QueryOptions $queryOptions = null
    ): array
    {
        return $this->getArticles(null, $statusId, $typeId, null, false, $queryOptions);
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
            'main_image_src' => $article->getMainImageSrc(),
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
