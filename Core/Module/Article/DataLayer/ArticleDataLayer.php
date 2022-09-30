<?php

namespace Amora\Core\Module\Article\Datalayer;

use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Module\Article\Model\ArticlePath;
use Amora\Core\Module\DataLayerTrait;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;
use Throwable;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\Logger;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\ArticleSection;
use Amora\Core\Module\User\DataLayer\UserDataLayer;
use Amora\Core\Module\Article\Value\ArticleStatus;

class ArticleDataLayer
{
    use DataLayerTrait;

    const ARTICLE_TABLE = 'core_article';
    const ARTICLE_HISTORY_TABLE = 'core_article_history';
    const ARTICLE_TYPE_TABLE = 'core_article_type';
    const ARTICLE_STATUS_TABLE = 'core_article_status';

    const ARTICLE_SECTION_TABLE = 'core_article_section';
    const ARTICLE_SECTION_TYPE_TABLE = 'core_article_section_type';
    const ARTICLE_SECTION_IMAGE_TABLE = 'core_article_section_image';

    const ARTICLE_TAG_RELATION_TABLE = 'core_article_tag_relation';

    const ARTICLE_PATH_TABLE = 'core_article_path';

    public function __construct(
        private readonly MySqlDb $db,
        private readonly Logger $logger,
        private readonly MediaDataLayer $mediaDataLayer,
        private readonly TagDataLayer $tagDataLayer,
    ) {}

    public function getDb(): MySqlDb
    {
        return $this->db;
    }

    public function filterArticlesBy(
        array $articleIds = [],
        array $languageIsoCodes = [],
        array $statusIds = [],
        array $typeIds = [],
        ?string $path = null,
        ?string $previousPath = null,
        array $tagIds = [],
        array $imageIds = [],
        bool $includeTags = false,
        bool $includePublishedAtInTheFuture = false,
        ?DateTimeImmutable $publishedBefore = null,
        ?DateTimeImmutable $publishedAfter = null,
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
            'a.language_iso_code AS article_language_iso_code',
            'a.user_id',
            'a.status_id',
            'a.type_id',
            'a.created_at AS article_created_at',
            'a.updated_at AS article_updated_at',
            'a.published_at',
            'a.title',
            'a.content_html',
            'a.main_image_id',
            'a.path AS article_path',

            'u.language_iso_code AS user_language_iso_code',
            'u.role_id AS user_role_id',
            'u.journey_id AS user_journey_id',
            'u.created_at AS user_created_at',
            'u.updated_at AS user_updated_at',
            'u.email AS user_email',
            'u.name AS user_name',
            'u.password_hash AS user_password_hash',
            'u.bio AS user_bio',
            'u.is_enabled AS user_is_enabled',
            'u.verified AS user_verified',
            'u.timezone AS user_timezone',
            'u.change_email_to AS user_change_email_to',

            'm.id AS media_id',
            'm.user_id AS media_user_id',
            'm.type_id AS media_type_id',
            'm.status_id AS media_status_id',
            'm.path AS media_path',
            'm.filename_original AS media_filename_original',
            'm.filename_medium AS media_filename_medium',
            'm.filename_large AS media_filename_large',
            'm.caption AS media_caption',
            'm.created_at AS media_created_at',
            'm.updated_at AS media_updated_at',
        ];

        $joins = ' FROM ' . self::ARTICLE_TABLE . ' AS a';
        $joins .= ' INNER JOIN ' . UserDataLayer::USER_TABLE . ' AS u ON u.id = a.user_id';
        $joins .= ' LEFT JOIN ' . MediaDataLayer::MEDIA_TABLE_NAME
            . ' AS m ON m.id = a.main_image_id';

        $where = ' WHERE 1';

        if ($articleIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $articleIds, 'a.id', 'articleId');
        }

        if ($languageIsoCodes) {
            $where .= $this->generateWhereSqlCodeForIds($params, $languageIsoCodes, 'a.language_iso_code', 'languageIsoCode');
        }

        if ($statusIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $statusIds, 'a.status_id', 'statusId');
        }

        if ($typeIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $typeIds, 'a.type_id', 'typeId');
        }

        if (isset($path)) {
            $where .= ' AND a.path = :articlePath';
            $params[':articlePath'] = $path;
        }

        if (isset($previousPath)) {
            $joins .= ' INNER JOIN ' . self::ARTICLE_PATH_TABLE . ' AS ap ON ap.article_id = a.id';
            $where .= ' AND ap.path = :previousPath';
            $params[':previousPath'] = $previousPath;
        }

        if ($tagIds) {
            $joins .= ' JOIN ' . ArticleDataLayer::ARTICLE_TAG_RELATION_TABLE
                . ' AS at ON at.article_id = a.id';

            $where .= $this->generateWhereSqlCodeForIds($params, $tagIds, 'at.tag_id', 'tagId');
        }

        if ($imageIds) {
            $joins .= ' LEFT JOIN ' . ArticleDataLayer::ARTICLE_SECTION_TABLE
                . ' AS aSec ON aSec.article_id = a.id';
            $joins .= ' LEFT JOIN ' . ArticleDataLayer::ARTICLE_SECTION_IMAGE_TABLE
                . ' AS aSecI ON aSecI.article_section_id = aSec.id';

            $where .= $this->generateWhereSqlCodeForIds($params, $imageIds, 'aSecI.media_id', 'imageId');
        }

        if (!$includePublishedAtInTheFuture) {
            $where .= ' AND a.published_at <= :publishedAt';
            $params[':publishedAt'] = DateUtil::getCurrentDateForMySql();
        }

        if (isset($publishedBefore)) {
            $where .= ' AND a.published_at < :publishedBefore';
            $params[':publishedBefore'] = $publishedBefore->format(DateUtil::MYSQL_DATETIME_FORMAT);
        }

        if (isset($publishedAfter)) {
            $where .= ' AND a.published_at > :publishedAfter';
            $params[':publishedAfter'] = $publishedAfter->format(DateUtil::MYSQL_DATETIME_FORMAT);
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

    public function filterArticlePathsBy(
        array $articleIds = [],
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
            'ap.id AS article_path_id',
            'ap.article_id AS article_path_article_id',
            'ap.created_at AS article_path_created_at',
            'ap.path AS article_path_path',
        ];

        $joins = ' FROM ' . self::ARTICLE_PATH_TABLE . ' AS ap';
        $where = ' WHERE 1';

        if ($articleIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $articleIds, 'ap.article_id', 'articleId');
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = ArticlePath::fromArray($item);
        }

        return $output;
    }

    public function updateArticle(Article $article): bool
    {
        $resUpdate = $this->db->update(
            tableName: self::ARTICLE_TABLE,
            id: $article->id,
            data: $article->asArray(),
        );

        if (empty($resUpdate)) {
            $this->logger->logError('Error updating article. Article ID: ' . $article->id);
            return false;
        }

        return true;
    }

    public function storeArticle(
        Article $article,
        ?string $userIp,
        ?string $userAgent
    ): ?Article {
        $resInsert = $this->db->insert(
            tableName: self::ARTICLE_TABLE,
            data: $article->asArray(),
        );

        if (empty($resInsert)) {
            $this->logger->logError('Error inserting article');
            return null;
        }

        $article->id = $resInsert;

        $resHistory = $this->storeArticleHistory(
            article: $article,
            userIp: $userIp,
            userAgent: $userAgent,
        );

        if (empty($resHistory)) {
            $this->logger->logError('Error inserting article history');
            return null;
        }

        return $article;
    }

    public function storeArticleHistory(Article $article, ?string $userIp, ?string $userAgent): bool
    {
        $data = [
            'article_id' => $article->id,
            'language_iso_code' => $article->language->value,
            'user_id' => $article->user->id,
            'status_id' => $article->status->value,
            'type_id' => $article->type->value,
            'created_at' => $article->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'title' => $article->title,
            'content_html' => $article->contentHtml,
            'main_image_id' => $article->mainImageId,
            'path' => $article->path,
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
                $resHistory = $this->storeArticleHistory($article, $userIp, $userAgent);
                if (empty($resHistory)) {
                    return new Feedback(false);
                }

                $resDe = $this->db->update(
                    self::ARTICLE_TABLE,
                    $article->id,
                    [
                        'status_id' => ArticleStatus::Deleted->value
                    ]
                );

                if (empty($resDe)) {
                    return new Feedback(false);
                }

                return new Feedback(true);
            }
        );

        return $resTransaction->isSuccess;
    }

    public function storeArticlePath(ArticlePath $articlePath): ArticlePath
    {
        $resInsert = $this->db->insert(
            tableName: self::ARTICLE_PATH_TABLE,
            data: $articlePath->asArray(),
        );

        $articlePath->id = $resInsert;

        return $articlePath;
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
                aSecI.media_id,
                aSecI.caption AS media_caption,
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

        $articleSection->id = $resInsert;

        return $articleSection;
    }

    public function updateArticleSection(ArticleSection $articleSection): bool
    {
        $array = $articleSection->asArray();
        unset($array['created_at']);
        $resUpdate = $this->db->update(
            self::ARTICLE_SECTION_TABLE,
            $articleSection->id,
            $array
        );

        if (empty($resUpdate)) {
            $this->logger->logError(
                'Error updating article section. Article Section ID: ' . $articleSection->id
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
                    'media_id' => $imageId,
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
                ' WHERE media_id = :mediaId
                    AND article_section_id = :articleSectionId',
                [
                    ':mediaId' => $imageId,
                    ':articleSectionId' => $articleSectionId,
                    ':caption' => $imageCaption
                ]
            );
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error updating entry into ' . ArticleDataLayer::ARTICLE_SECTION_IMAGE_TABLE .
                ' - MediaId: ' . $imageId .
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
            ' WHERE media_id = :mediaId
                AND article_section_id = :articleSectionId',
            [
                ':mediaId' => $imageId,
                ':articleSectionId' => $articleSectionId
            ]
        );
    }
}
