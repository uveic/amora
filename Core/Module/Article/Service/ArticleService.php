<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Core;
use Amora\Core\Database\Model\TransactionResponse;
use Amora\Core\Logger;
use Amora\Core\Model\Util\QueryOptions;
use Amora\Core\Model\Util\QueryOrderBy;
use Amora\Core\Module\Article\Datalayer\ArticleDataLayer;
use Amora\Core\Module\Article\Datalayer\TagDataLayer;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\ArticleSection;
use Amora\Core\Module\Article\Model\Tag;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;

class ArticleService
{
    public function __construct(
        private Logger $logger,
        private ArticleDataLayer $articleDataLayer,
        private TagDataLayer $tagDataLayer,
    ) {}

    public function getArticleForId(int $id, bool $includeTags = false): ?Article
    {
        return $this->articleDataLayer->getArticleForId($id, $includeTags);
    }

    public function getArticlesForTypeIds(array $articleTypeIds): array
    {
        return $this->articleDataLayer->getArticlesForTypeIds($articleTypeIds);
    }

    public function getArticleForUri(string $uri): ?Article
    {
        return $this->articleDataLayer->getArticleForUri($uri);
    }

    public function filterArticlesBy(
        array $articleIds = [],
        array $statusIds = [],
        array $typeIds = [],
        ?string $uri = null,
        array $tagIds = [],
        bool $includeTags = false,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->articleDataLayer->filterArticlesBy(
            articleIds: $articleIds,
            statusIds: $statusIds,
            typeIds: $typeIds,
            uri: $uri,
            tagIds: $tagIds,
            includeTags: $includeTags,
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
            statusIds: [ArticleStatus::PUBLISHED],
            typeIds: [ArticleType::HOMEPAGE],
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
        $articleId = $existingArticle?->getId();

        if (!$uri && !$articleTitle) {
            $uri = strtolower(StringUtil::getRandomString(32));
        } else if (!$uri && $articleTitle) {
            $uri = strtolower(StringUtil::cleanString($articleTitle));
        } else {
            $uri = $uri === $existingArticle->getUri() && $articleTitle
                ? strtolower(StringUtil::cleanString($articleTitle))
                : $uri;
        }

        $count = 0;
        do {
            $validUri = $uri . ($count > 0 ? '-' . $count : '');
            $res = $this->getArticleForUri($validUri);
            if ($articleId && $res && $res->getId() === $articleId) {
                $res = null;
            }
            $count++;
        } while(!empty($res));

        return $validUri;
    }

    public function updateArticle(
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
                        'Error updating article. Article ID: ' . $article->getId()
                    );
                    return new TransactionResponse(false);
                }

                $resSections = $this->updateCreateOrDeleteArticleSections(
                    $article->getId(),
                    $sections
                );

                if (empty($resSections)) {
                    $this->logger->logError(
                        'Error updating article sections. Article ID: ' . $article->getId()
                    );
                    return new TransactionResponse(false);
                }

                $resTags = $this->addOrRemoveTagsToArticle($article->getId(), $tags);
                if (empty($resTags)) {
                    $this->logger->logError(
                        'Error updating article tags. Article ID: ' . $article->getId()
                    );
                    return new TransactionResponse(false);
                }

                $resHistory = $this->articleDataLayer->insertArticleHistory(
                    $article,
                    $userIp,
                    $userAgent
                );

                if (empty($resHistory)) {
                    $this->logger->logError(
                        'Error inserting article history. Article ID: ' . $article->getId()
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
            $articleSectionsById[$existingSection->getId()] = $existingSection;
        }

        $now = DateUtil::getCurrentDateForMySql();
        $newSections = [];
        foreach ($sections as $section) {
            $newSectionId = isset($section['id']) ? (int)$section['id'] : null;
            $newSections[] = new ArticleSection(
                $newSectionId,
                $articleId,
                (int)$section['sectionTypeId'],
                html_entity_decode($section['contentHtml']),
                isset($section['order']) ? (int)$section['order'] : null,
                isset($section['imageId']) ? (int)$section['imageId'] : null,
                $section['imageCaption'] ?? null,
                $newSectionId && isset($articleSectionsById[$newSectionId])
                    ? $articleSectionsById[$newSectionId]->getUpdatedAt()
                    : $now,
                $now
            );
        }

        /** @var ArticleSection $section */
        foreach ($newSections as $nSection) {
            if ($nSection->getId() && $articleSectionsById[$nSection->getId()]) {
                $res = $this->updateArticleSection($nSection);

                if (empty($res)) {
                    $this->logger->logError(
                        'Error updating article section. Article Section ID: ' . $nSection->getId()
                    );
                    return false;
                }

                unset($articleSectionsById[$nSection->getId()]);
            } else {
                $res = $this->createArticleSection($nSection);

                if (empty($res)) {
                    $this->logger->logError(
                        'Error updating inserting section. Article Section ID: ' . $nSection->getId()
                    );
                    return false;
                }
            }
        }

        foreach ($articleSectionsById as $articleSection) {
            if ($articleSection->getImageId()) {
                $this->articleDataLayer->deleteArticleSectionImage(
                    $articleSection->getId(),
                    $articleSection->getImageId()
                );
            }
            $this->articleDataLayer->deleteArticleSection($articleSection->getId());
        }

        return true;
    }

    private function addOrRemoveTagsToArticle(int $articleId, array $tags): bool
    {
        $existingTags = $this->tagDataLayer->getTagsForArticleId($articleId);
        $existingTagsById = [];
        /** @var Tag $existingTag */
        foreach ($existingTags as $existingTag) {
            $existingTagsById[$existingTag->getId()] = $existingTag;
        }

        $newTags = [];
        foreach ($tags as $tag) {
            $newTagId = isset($tag['id']) ? (int)$tag['id'] : null;
            $newTags[] = new Tag($newTagId, $tag['name']);
        }

        /** @var Tag $nTag */
        foreach ($newTags as $nTag) {
            if (empty($nTag->getId())) {
                // ToDo: implement validation at controller level to avoid getting here
                // This shouldn't happen ever
                $this->logger->logError('Tag missing ID. This should not have happened.');
                continue;
            }

            if (isset($existingTagsById[$nTag->getId()])) {
                unset($existingTagsById[$nTag->getId()]);
                continue;
            }

            $resRelation = $this->tagDataLayer->insertArticleTagRelation(
                $nTag->getId(),
                $articleId
            );

            if (empty($resRelation)) {
                $this->logger->logError('Error inserting article/tag relation');
                return false;
            }
        }

        foreach ($existingTagsById as $tag) {
            $this->tagDataLayer->deleteArticleTagRelation($tag->getId(), $articleId);
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
                    $article,
                    $userIp,
                    $userAgent
                );

                if (empty($article)) {
                    $this->logger->logError(
                        'Error creating article. Article ID: ' . $article->getId()
                    );
                    return new TransactionResponse(false);
                }

                $resSections = $this->updateCreateOrDeleteArticleSections(
                    $article->getId(),
                    $sections
                );

                $resTags = $this->addOrRemoveTagsToArticle($article->getId(), $tags);
                if (empty($resTags)) {
                    $this->logger->logError(
                        'Error updating article tags. Article ID: ' . $article->getId()
                    );
                    return new TransactionResponse(false);
                }

                if (empty($resSections)) {
                    $this->logger->logError(
                        'Error updating article sections. Article ID: ' . $article->getId()
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

        if ($section->getImageId()) {
            $resImage = $this->articleDataLayer->createArticleSectionImage(
                $section->getId(),
                $section->getImageId(),
                $section->getImageCaption()
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

        if ($section->getImageId()) {
            $resImage = $this->articleDataLayer->updateArticleSectionImage(
                $section->getId(),
                $section->getImageId(),
                $section->getImageCaption()
            );

            if (empty($resImage)) {
                return false;
            }
        }

        return true;
    }
}
