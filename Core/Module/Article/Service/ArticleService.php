<?php

namespace Amora\Core\Module\Article\Service;

use Amora\App\Router\AppRouter;
use Amora\App\Value\Language;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Module\Article\Model\ArticleUri;
use Amora\Core\Util\Logger;
use Amora\Core\Entity\Response\Pagination;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Article\Datalayer\ArticleDataLayer;
use Amora\Core\Module\Article\Datalayer\TagDataLayer;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\ArticleSection;
use Amora\Core\Module\Article\Model\Tag;
use Amora\Core\Module\Article\Value\ArticleSectionType;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\StringUtil;
use Amora\Core\Value\QueryOrderDirection;
use DateTimeImmutable;

class ArticleService
{
    public function __construct(
        private readonly Logger $logger,
        private readonly ArticleDataLayer $articleDataLayer,
        private readonly TagDataLayer $tagDataLayer,
    ) {}

    public function getArticleForId(
        int $id,
        bool $includeTags = false,
        ?Language $language = null,
    ): ?Article {
        $res = $this->filterArticlesBy(
            articleIds: [$id],
            languageIsoCodes: $language ? [$language->value] : [],
            includeTags: $includeTags,
            includePublishedAtInTheFuture: true,
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getArticleForUri(string $uri, bool $includePublishedAtInTheFuture = true): ?Article
    {
        $res = $this->filterArticlesBy(
            typeIds: [ArticleType::Blog->value, ArticleType::Page->value],
            uri: $uri,
            includePublishedAtInTheFuture: $includePublishedAtInTheFuture,
        );

        if (isset($res[0])) {
            return $res[0];
        }

        $res = $this->filterArticlesBy(
            typeIds: [ArticleType::Blog->value, ArticleType::Page->value],
            previousUri: $uri,
            includePublishedAtInTheFuture: $includePublishedAtInTheFuture,
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getPreviousBlogPost(
        DateTimeImmutable $publishedBefore,
        bool $isAdmin = false,
    ): ?Article {
        $statusIds = $isAdmin
            ? [ArticleStatus::Private->value, ArticleStatus::Published->value]
            : [ArticleStatus::Published->value];

        $res = $this->filterArticlesBy(
            statusIds: $statusIds,
            typeIds: [ArticleType::Blog->value],
            publishedBefore: $publishedBefore,
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy(field: 'published_at', direction: QueryOrderDirection::DESC)],
                pagination: new Pagination(itemsPerPage: 1),
            ),
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getNextBlogPost(DateTimeImmutable $publishedAfter, bool $isAdmin = false): ?Article
    {
        $statusIds = $isAdmin
            ? [ArticleStatus::Private->value, ArticleStatus::Published->value]
            : [ArticleStatus::Published->value];

        $res = $this->filterArticlesBy(
            statusIds: $statusIds,
            typeIds: [ArticleType::Blog->value],
            publishedAfter: $publishedAfter,
            queryOptions: new QueryOptions(
            orderBy: [new QueryOrderBy(field: 'published_at', direction: QueryOrderDirection::ASC)],
                pagination: new Pagination(itemsPerPage: 1),
            ),
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function filterArticlesBy(
        array $articleIds = [],
        array $languageIsoCodes = [],
        array $statusIds = [],
        array $typeIds = [],
        ?string $uri = null,
        ?string $previousUri = null,
        array $tagIds = [],
        array $imageIds = [],
        bool $includeTags = false,
        bool $includePublishedAtInTheFuture = false,
        ?DateTimeImmutable $publishedBefore = null,
        ?DateTimeImmutable $publishedAfter = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->articleDataLayer->filterArticlesBy(
            articleIds: $articleIds,
            languageIsoCodes: $languageIsoCodes,
            statusIds: $statusIds,
            typeIds: $typeIds,
            uri: $uri,
            previousUri: $previousUri,
            tagIds: $tagIds,
            imageIds: $imageIds,
            includeTags: $includeTags,
            includePublishedAtInTheFuture: $includePublishedAtInTheFuture,
            publishedBefore: $publishedBefore,
            publishedAfter: $publishedAfter,
            queryOptions: $queryOptions,
        );
    }

    public function filterPreviousArticleUrisBy(
        array $articleIds = [],
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->articleDataLayer->filterPreviousArticleUrisBy(
            articleIds: $articleIds,
            queryOptions: $queryOptions,
        );
    }

    public function getSectionsForArticleId(int $articleId): array
    {
        return $this->articleDataLayer->getSectionsForArticleId($articleId);
    }

    public function getArticlePartialContent(ArticleType $articleType, Language $language): ?Article
    {
        $res = $this->filterArticlesBy(
            languageIsoCodes: [$language->value],
            statusIds: [ArticleStatus::Published->value],
            typeIds: [$articleType->value],
        );

        if (count($res) > 1) {
            $this->logger->logError(
                'There are more than one homepage article. Returning the most recently updated.'
                . ' Language: ' . $language->value,
            );
        }

        return empty($res[0]) ? null : $res[0];
    }

    public function getAvailableUriForArticle(
        ?string $uri = null,
        ?string $articleTitle = null,
        ?Article $existingArticle = null
    ): string {
        $articleId = $existingArticle?->id;
        $uri = $uri ? strtolower(StringUtil::cleanString($uri)) : null;

        if (!$uri && !$articleTitle) {
            $uri = strtolower(StringUtil::getRandomString(32));
        } else if (!$uri && $articleTitle) {
            $uri = strtolower(StringUtil::cleanString($articleTitle));
        } else {
            $uri = $uri === $existingArticle->uri && $articleTitle
                ? strtolower(StringUtil::cleanString($articleTitle))
                : $uri;
        }

        $count = 0;
        do {
            $validUri = $uri . ($count > 0 ? '-' . $count : '');
            $res = $this->getArticleForUri($validUri);
            if ($articleId && $res && $res->id === $articleId) {
                $res = null;
            }
            $count++;
        } while(!empty($res));

        $count = 0;
        do {
            $validUri = $uri . ($count > 0 ? '-' . $count : '');
            $res = in_array($validUri, AppRouter::getReservedPaths());
            $count++;
        } while($res);

        return $validUri;
    }

    public function workflowUpdateArticle(
        Article $article,
        array $sections,
        array $tags,
        ?string $userIp,
        ?string $userAgent,
    ): bool {
        $resTransaction = $this->articleDataLayer->getDb()->withTransaction(
            function () use ($article, $sections, $tags, $userIp, $userAgent) {
                $resUpdate = $this->articleDataLayer->updateArticle($article);

                if (empty($resUpdate)) {
                    $this->logger->logError(
                        'Error updating article. Article ID: ' . $article->id
                    );
                    return new Feedback(false);
                }

                $resSections = $this->updateCreateOrDeleteArticleSections(
                    articleId: $article->id,
                    sections: $sections,
                );

                if (empty($resSections)) {
                    $this->logger->logError(
                        'Error updating article sections. Article ID: ' . $article->id
                    );
                    return new Feedback(false);
                }

                $resTags = $this->addOrRemoveTagsToArticle($article->id, $tags);
                if (empty($resTags)) {
                    $this->logger->logError(
                        'Error updating article tags. Article ID: ' . $article->id
                    );
                    return new Feedback(false);
                }

                $resHistory = $this->articleDataLayer->storeArticleHistory(
                    article: $article,
                    userIp: $userIp,
                    userAgent: $userAgent,
                );

                if (empty($resHistory)) {
                    $this->logger->logError(
                        'Error inserting article history. Article ID: ' . $article->id
                    );
                    return new Feedback(false);
                }

                return new Feedback(true);
            }
        );

        return $resTransaction->isSuccess;
    }

    private function updateCreateOrDeleteArticleSections(int $articleId, array $sections): bool
    {
        $existingSections = $this->articleDataLayer->getSectionsForArticleId($articleId);
        $articleSectionsById = [];
        /** @var ArticleSection $existingSection */
        foreach ($existingSections as $existingSection) {
            $articleSectionsById[$existingSection->id] = $existingSection;
        }

        $now = new DateTimeImmutable();
        $newSections = [];
        foreach ($sections as $section) {
            $newSectionId = isset($section['id']) ? (int)$section['id'] : null;
            $newSections[] = new ArticleSection(
                id: $newSectionId,
                articleId: $articleId,
                articleSectionType: ArticleSectionType::from($section['sectionTypeId']),
                contentHtml: html_entity_decode($section['contentHtml']),
                order: isset($section['order']) ? (int)$section['order'] : null,
                mediaId: isset($section['imageId']) ? (int)$section['imageId'] : null,
                mediaCaption: $section['imageCaption'] ?? null,
                createdAt: $newSectionId && isset($articleSectionsById[$newSectionId])
                    ? $articleSectionsById[$newSectionId]->updatedAt
                    : $now,
                updatedAt: $now,
            );
        }

        /** @var ArticleSection $section */
        foreach ($newSections as $nSection) {
            if ($nSection->id && $articleSectionsById[$nSection->id]) {
                $res = $this->updateArticleSection($nSection);

                if (empty($res)) {
                    $this->logger->logError(
                        'Error updating article section. Article Section ID: ' . $nSection->id
                    );
                    return false;
                }

                unset($articleSectionsById[$nSection->id]);
            } else {
                $res = $this->createArticleSection($nSection);

                if (empty($res)) {
                    $this->logger->logError(
                        'Error updating inserting section. Article Section ID: ' . $nSection->id
                    );
                    return false;
                }
            }
        }

        foreach ($articleSectionsById as $articleSection) {
            if ($articleSection->mediaId) {
                $this->articleDataLayer->deleteArticleSectionImage(
                    $articleSection->id,
                    $articleSection->mediaId,
                );
            }
            $this->articleDataLayer->deleteArticleSection($articleSection->id);
        }

        return true;
    }

    private function addOrRemoveTagsToArticle(int $articleId, array $tags): bool
    {
        $existingTags = $this->tagDataLayer->getTagsForArticleId($articleId);
        $existingTagsById = [];
        /** @var Tag $existingTag */
        foreach ($existingTags as $existingTag) {
            $existingTagsById[$existingTag->id] = $existingTag;
        }

        $newTags = [];
        foreach ($tags as $tag) {
            $newTagId = isset($tag['id']) ? (int)$tag['id'] : null;
            $newTags[] = new Tag($newTagId, $tag['name']);
        }

        /** @var Tag $nTag */
        foreach ($newTags as $nTag) {
            if (empty($nTag->id)) {
                // ToDo: implement validation at controller level to avoid getting here
                // This shouldn't happen ever
                $this->logger->logError('Tag missing ID. This should not have happened.');
                continue;
            }

            if (isset($existingTagsById[$nTag->id])) {
                unset($existingTagsById[$nTag->id]);
                continue;
            }

            $resRelation = $this->tagDataLayer->insertArticleTagRelation(
                tagId: $nTag->id,
                articleId: $articleId,
            );

            if (empty($resRelation)) {
                $this->logger->logError('Error inserting article/tag relation');
                return false;
            }
        }

        foreach ($existingTagsById as $tag) {
            $this->tagDataLayer->deleteArticleTagRelation($tag->id, $articleId);
        }

        return true;
    }

    public function deleteArticle(Article $article, ?string $userIp, ?string $userAgent): bool
    {
        return $this->articleDataLayer->deleteArticle($article, $userIp, $userAgent);
    }

    public function createNewArticle(
        Article $article,
        array $sections,
        array $tags,
        ?string $userIp,
        ?string $userAgent,
    ): ?Article {
        $resTransaction = $this->articleDataLayer->getDb()->withTransaction(
            function () use ($article, $sections, $tags, $userIp, $userAgent) {
                $article = $this->articleDataLayer->storeArticle(
                    article: $article,
                    userIp: $userIp,
                    userAgent: $userAgent,
                );

                if (empty($article)) {
                    $this->logger->logError(
                        'Error creating article. Article ID: ' . $article->id
                    );

                    return new Feedback(false);
                }

                $resSections = $this->updateCreateOrDeleteArticleSections(
                    articleId: $article->id,
                    sections: $sections,
                );

                $resTags = $this->addOrRemoveTagsToArticle($article->id, $tags);
                if (empty($resTags)) {
                    $this->logger->logError(
                        'Error updating article tags. Article ID: ' . $article->id
                    );
                    return new Feedback(false);
                }

                if (empty($resSections)) {
                    $this->logger->logError(
                        'Error updating article sections. Article ID: ' . $article->id
                    );
                    return new Feedback(false);
                }

                return new Feedback(true, $article);
            }
        );

        return $resTransaction->isSuccess ? $resTransaction->response : null;
    }

    private function createArticleSection(ArticleSection $section): bool
    {
        $res = $this->articleDataLayer->createArticleSection($section);

        if (empty($res)) {
            return false;
        }

        if ($section->mediaId) {
            $resImage = $this->articleDataLayer->createArticleSectionImage(
                articleSectionId: $section->id,
                imageId: $section->mediaId,
                imageCaption: $section->mediaCaption,
            );

            if (empty($resImage)) {
                return false;
            }
        }

        return true;
    }

    private function updateArticleSection(ArticleSection $section): bool
    {
        $res = $this->articleDataLayer->updateArticleSection($section);

        if (empty($res)) {
            return false;
        }

        if ($section->mediaId) {
            $resImage = $this->articleDataLayer->updateArticleSectionImage(
                articleSectionId: $section->id,
                imageId: $section->mediaId,
                imageCaption: $section->mediaCaption,
            );

            if (empty($resImage)) {
                return false;
            }
        }

        return true;
    }

    public function storeArticleUri(ArticleUri $articleUri): ArticleUri
    {
        return $this->articleDataLayer->storeArticleUri($articleUri);
    }
}
