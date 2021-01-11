<?php

namespace uve\core\module\article\service;

use uve\core\Logger;
use uve\core\module\article\datalayer\ArticleDataLayer;
use uve\core\module\article\model\Article;
use uve\core\module\article\model\ArticleSection;
use uve\core\module\article\value\ArticleStatus;
use uve\core\util\DateUtil;

class ArticleService
{
    private Logger $logger;
    private ArticleDataLayer $articleDataLayer;
    private ImageService $imageService;

    public function __construct(
        Logger $logger,
        ArticleDataLayer $articleDataLayer,
        ImageService $imageService
    ) {
        $this->logger = $logger;
        $this->articleDataLayer = $articleDataLayer;
        $this->imageService = $imageService;
    }

    public function getArticleForId(int $id): ?Article
    {
        return $this->articleDataLayer->getArticleForId($id);
    }

    public function getArticleForUri(string $uri): ?Article
    {
        return $this->articleDataLayer->getArticleForUri($uri);
    }

    public function getAllArticles(): array
    {
        return $this->articleDataLayer->getAllArticles();
    }

    public function getSectionsForArticleId(int $articleId): array
    {
        return $this->articleDataLayer->getSectionsForArticleId($articleId);
    }

    public function updateArticle(
        Article $article,
        array $sections,
        ?string $userIp,
        ?string $userAgent
    ): bool {
        $resTransaction = $this->articleDataLayer->getDb()->withTransaction(
            function () use ($article, $sections, $userIp, $userAgent) {
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
                $section['contentHtml'],
                isset($section['order']) ? (int)$section['order'] : null,
                $newSectionId && isset($articleSectionsById[$newSectionId])
                    ? $articleSectionsById[$newSectionId]->getUpdatedAt()
                    : $now,
                $now
            );
        }

        /** @var ArticleSection $section */
        foreach ($newSections as $nSection) {
            if ($nSection->getId() && $articleSectionsById[$nSection->getId()]) {
                $res = $this->articleDataLayer->updateArticleSection($nSection);

                if (empty($res)) {
                    $this->logger->logError(
                        'Error updating article section. Article Section ID: ' . $nSection->getId()
                    );
                    return false;
                }

                unset($articleSectionsById[$nSection->getId()]);
            } else {
                $res = $this->articleDataLayer->createArticleSection($nSection);

                if (empty($res)) {
                    $this->logger->logError(
                        'Error updating inserting section. Article Section ID: ' . $nSection->getId()
                    );
                    return false;
                }
            }
        }

        foreach ($articleSectionsById as $articleSection) {
            $this->articleDataLayer->deleteArticleSection($articleSection->getId());
        }

        return true;
    }

    public function deleteArticle(Article $article, ?string $userIp, ?string $userAgent): bool
    {
        return $this->articleDataLayer->deleteArticle($article, $userIp, $userAgent);
    }

    public function createNewArticle(
        Article $article,
        ?string $userIp,
        ?string $userAgent
    ): Article {
        return $this->articleDataLayer->createNewArticle($article, $userIp, $userAgent);
    }

    public function getArticlesForHome(): array
    {
        return $this->articleDataLayer->getArticlesWithStatus(ArticleStatus::PUBLISHED);
    }
}
