<?php

namespace uve\core\module\article\service;

use uve\core\Logger;
use uve\Core\Model\Util\QueryOptions;
use uve\core\module\article\datalayer\ArticleDataLayer;
use uve\core\module\article\datalayer\TagDataLayer;
use uve\core\module\article\model\Article;
use uve\core\module\article\model\ArticleSection;
use uve\core\module\article\model\Tag;
use uve\core\module\article\value\ArticleStatus;
use uve\core\module\article\value\ArticleType;
use uve\core\util\DateUtil;

class ArticleService
{
    public function __construct(
        private Logger $logger,
        private ArticleDataLayer $articleDataLayer,
        private TagDataLayer $tagDataLayer,
        private ImageService $imageService,
    ) {}

    public function getArticleForId(int $id, bool $includeTags = false): ?Article
    {
        return $this->articleDataLayer->getArticleForId($id, $includeTags);
    }

    public function getArticleForUri(string $uri): ?Article
    {
        return $this->articleDataLayer->getArticleForUri($uri);
    }

    public function getAllArticles(?QueryOptions $queryOptions): array
    {
        return $this->articleDataLayer->getAllArticles($queryOptions);
    }

    public function getSectionsForArticleId(int $articleId): array
    {
        return $this->articleDataLayer->getSectionsForArticleId($articleId);
    }

    public function checkUriAndReturnAnAvailableOne(string $uri, int $articleId = null): string
    {
        $count = 0;
        $validUri = null;
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
                    return ['success' => false];
                }

                $resSections = $this->updateCreateOrDeleteArticleSections(
                    $article->getId(),
                    $sections
                );

                if (empty($resSections)) {
                    $this->logger->logError(
                        'Error updating article sections. Article ID: ' . $article->getId()
                    );
                    return ['success' => false];
                }

                $resTags = $this->addOrRemoveTagsToArticle($article->getId(), $tags);
                if (empty($resTags)) {
                    $this->logger->logError(
                        'Error updating article tags. Article ID: ' . $article->getId()
                    );
                    return ['success' => false];
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
                    return ['success' => false];
                }

                return [
                    'success' => true,
                    'article' => $article
                ];
            }
        );

        return $resTransaction['success'];
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

            if ($existingTagsById[$nTag->getId()]) {
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
                    return ['success' => false];
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
                    return ['success' => false];
                }

                if (empty($resSections)) {
                    $this->logger->logError(
                        'Error updating article sections. Article ID: ' . $article->getId()
                    );
                    return ['success' => false];
                }

                return [
                    'success' => true,
                    'article' => $article
                ];
            }
        );

        return $resTransaction['article'] ?? null;
    }

    public function getArticlesForHome(bool $isAdmin = false): array
    {
        if ($isAdmin) {
            return $this->articleDataLayer->filterArticlesBy();
        }

        return $this->articleDataLayer->filterArticlesBy(
            ArticleStatus::PUBLISHED,
            ArticleType::HOME
        );
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
