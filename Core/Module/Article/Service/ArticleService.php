<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Database\Model\TransactionResponse;
use Amora\Core\Util\Logger;
use Amora\Core\Model\Response\Pagination;
use Amora\Core\Model\Util\QueryOptions;
use Amora\Core\Model\Util\QueryOrderBy;
use Amora\Core\Module\Article\ArticleCore;
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
        private Logger $logger,
        private ArticleDataLayer $articleDataLayer,
        private TagDataLayer $tagDataLayer,
    ) {}

    public function getArticleForId(int $id, bool $includeTags = false): ?Article
    {
        $res = $this->filterArticlesBy(
            articleIds: [$id],
            includeTags: $includeTags,
            includePublishedAtInTheFuture: true,
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getArticleForUri(string $uri): ?Article
    {
        $res = $this->filterArticlesBy(
            uri: $uri,
            includePublishedAtInTheFuture: true,
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
        array $statusIds = [],
        array $typeIds = [],
        ?string $uri = null,
        array $tagIds = [],
        bool $includeTags = false,
        bool $includePublishedAtInTheFuture = false,
        ?DateTimeImmutable $publishedBefore = null,
        ?DateTimeImmutable $publishedAfter = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->articleDataLayer->filterArticlesBy(
            articleIds: $articleIds,
            statusIds: $statusIds,
            typeIds: $typeIds,
            uri: $uri,
            tagIds: $tagIds,
            includeTags: $includeTags,
            includePublishedAtInTheFuture: $includePublishedAtInTheFuture,
            publishedBefore: $publishedBefore,
            publishedAfter: $publishedAfter,
            queryOptions: $queryOptions,
        );
    }

    public function getSectionsForArticleId(int $articleId): array
    {
        return $this->articleDataLayer->getSectionsForArticleId($articleId);
    }

    public function getHomepageArticle(): ?Article
    {
        $res = $this->filterArticlesBy(
            statusIds: [ArticleStatus::Published->value],
            typeIds: [ArticleType::Homepage->value],
        );

        if (count($res) > 1) {
            $this->logger->logError(
                'There are more than one homepage article. Returning the most recently updated.'
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

        return $validUri;
    }

    public function workflowUpdateArticle(
        Article $article,
        array $sections,
        array $tags,
        ?string $userIp,
        ?string $userAgent
    ): bool {
        $resTransaction = $this->articleDataLayer->getDb()->withTransaction(
            function () use ($article, $sections, $tags, $userIp, $userAgent) {
                $resUpdate = $this->articleDataLayer->updateArticle($article);

                if (empty($resUpdate)) {
                    $this->logger->logError(
                        'Error updating article. Article ID: ' . $article->id
                    );
                    return new TransactionResponse(false);
                }

                $resSections = $this->updateCreateOrDeleteArticleSections(
                    $article->id,
                    $sections
                );

                if (empty($resSections)) {
                    $this->logger->logError(
                        'Error updating article sections. Article ID: ' . $article->id
                    );
                    return new TransactionResponse(false);
                }

                $resTags = $this->addOrRemoveTagsToArticle($article->id, $tags);
                if (empty($resTags)) {
                    $this->logger->logError(
                        'Error updating article tags. Article ID: ' . $article->id
                    );
                    return new TransactionResponse(false);
                }

                $resHistory = $this->articleDataLayer->insertArticleHistory(
                    article: $article,
                    userIp: $userIp,
                    userAgent: $userAgent,
                );

                if (empty($resHistory)) {
                    $this->logger->logError(
                        'Error inserting article history. Article ID: ' . $article->id
                    );
                    return new TransactionResponse(false);
                }

                return new TransactionResponse(true);
            }
        );

        return $resTransaction->isSuccess();
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
                imageId: isset($section['imageId']) ? (int)$section['imageId'] : null,
                imageCaption: $section['imageCaption'] ?? null,
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
            if ($articleSection->imageId) {
                $this->articleDataLayer->deleteArticleSectionImage(
                    $articleSection->id,
                    $articleSection->imageId,
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
        ?string $userAgent
    ): ?Article {
        $resTransaction = $this->articleDataLayer->getDb()->withTransaction(
            function () use ($article, $sections, $tags, $userIp, $userAgent) {
                $article = $this->articleDataLayer->createNewArticle(
                    article: $article,
                    userIp: $userIp,
                    userAgent: $userAgent,
                );

                if (empty($article)) {
                    $this->logger->logError(
                        'Error creating article. Article ID: ' . $article->id
                    );
                    return new TransactionResponse(false);
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
                    return new TransactionResponse(false);
                }

                if (empty($resSections)) {
                    $this->logger->logError(
                        'Error updating article sections. Article ID: ' . $article->id
                    );
                    return new TransactionResponse(false);
                }

                return new TransactionResponse(true, $article);
            }
        );

        return $resTransaction->isSuccess() ? $resTransaction->getResponse() : null;
    }

    private function createArticleSection(ArticleSection $section): bool
    {
        $res = $this->articleDataLayer->createArticleSection($section);

        if (empty($res)) {
            return false;
        }

        if ($section->imageId) {
            $resImage = $this->articleDataLayer->createArticleSectionImage(
                $section->id,
                $section->imageId,
                $section->imageCaption,
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

        if ($section->imageId) {
            $resImage = $this->articleDataLayer->updateArticleSectionImage(
                $section->id,
                $section->imageId,
                $section->imageCaption,
            );

            if (empty($resImage)) {
                return false;
            }
        }

        return true;
    }
}
