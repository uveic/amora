<?php

namespace Amora\Core\Module\Article\Service;

use Amora\App\Module\Form\Entity\PageContent;
use Amora\App\Router\AppRouter;
use Amora\App\Value\AppPageContentType;
use Amora\App\Value\Language;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Module\Album\Model\Collection;
use Amora\Core\Module\Article\Entity\FeedItem;
use Amora\Core\Module\Article\Model\ArticlePath;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\PageContentType;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\Logger;
use Amora\Core\Entity\Response\Pagination;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Article\DataLayer\ArticleDataLayer;
use Amora\Core\Module\Article\DataLayer\TagDataLayer;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\ArticleSection;
use Amora\Core\Module\Article\Model\Tag;
use Amora\Core\Module\Article\Value\ArticleSectionType;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\QueryOrderDirection;
use DateTimeImmutable;

readonly class ArticleService
{
    public function __construct(
        private Logger $logger,
        private ArticleDataLayer $articleDataLayer,
        private TagDataLayer $tagDataLayer,
    ) {}

    public function getArticleForId(
        int $id,
        bool $includeTags = false,
        ?Language $language = null,
    ): ?Article {
        $res = $this->filterArticleBy(
            articleIds: [$id],
            languageIsoCodes: $language ? [$language->value] : [],
            includeTags: $includeTags,
            includePublishedAtInTheFuture: true,
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getMediaForArticleId(int $articleId): array
    {
        return $this->articleDataLayer->filterArticleMediaBy(
            articleIds: [$articleId],
        );
    }

    public function getArticleForPath(string $path, bool $includePublishedAtInTheFuture = true): ?Article
    {
        $res = $this->filterArticleBy(
            typeIds: [ArticleType::Blog->value, ArticleType::Page->value],
            path: $path,
            includePublishedAtInTheFuture: $includePublishedAtInTheFuture,
        );

        if (isset($res[0])) {
            return $res[0];
        }

        $res = $this->filterArticleBy(
            typeIds: [ArticleType::Blog->value, ArticleType::Page->value],
            previousPath: $path,
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

        $res = $this->filterArticleBy(
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
            ? [ArticleStatus::Private->value, ArticleStatus::Unlisted->value, ArticleStatus::Published->value]
            : [ArticleStatus::Published->value];

        $res = $this->filterArticleBy(
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

    public function filterArticleBy(
        array $articleIds = [],
        array $languageIsoCodes = [],
        array $statusIds = [],
        array $typeIds = [],
        ?string $path = null,
        ?string $previousPath = null,
        array $tagIds = [],
        array $imageIds = [],
        ?string $searchQuery = null,
        bool $includeTags = false,
        bool $includePublishedAtInTheFuture = false,
        ?DateTimeImmutable $publishedBefore = null,
        ?DateTimeImmutable $publishedAfter = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->articleDataLayer->filterArticleBy(
            articleIds: $articleIds,
            languageIsoCodes: $languageIsoCodes,
            statusIds: $statusIds,
            typeIds: $typeIds,
            path: $path,
            previousPath: $previousPath,
            tagIds: $tagIds,
            imageIds: $imageIds,
            searchQuery: $searchQuery,
            includeTags: $includeTags,
            includePublishedAtInTheFuture: $includePublishedAtInTheFuture,
            publishedBefore: $publishedBefore,
            publishedAfter: $publishedAfter,
            queryOptions: $queryOptions,
        );
    }

    public function filterArticlePathsBy(
        array $articleIds = [],
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->articleDataLayer->filterArticlePathsBy(
            articleIds: $articleIds,
            queryOptions: $queryOptions,
        );
    }

    public function filterPageContentBy(
        array $ids = [],
        array $languageIsoCodes = [],
        array $typeIds = [],
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->articleDataLayer->filterPageContentBy(
            ids: $ids,
            languageIsoCodes: $languageIsoCodes,
            typeIds: $typeIds,
            queryOptions: $queryOptions,
        );
    }

    public function getSectionsForArticleId(int $articleId): array
    {
        return $this->articleDataLayer->getSectionsForArticleId($articleId);
    }

    public function getPageContent(PageContentType|AppPageContentType $type, Language $language): ?PageContent
    {
        $res = $this->filterPageContentBy(
            languageIsoCodes: [$language->value],
            typeIds: [$type->value],
        );

        if (count($res) > 1) {
            $this->logger->logError(
                'There are more than one homepage article. Returning the most recently updated.'
                . ' Language: ' . $language->value,
            );
        }

        return empty($res[0]) ? null : $res[0];
    }

    public function getAvailablePathForArticle(
        ?string $articleTitle = null,
        ?Article $existingArticle = null,
        ?ArticleStatus $articleStatus = null,
    ): string {
        $articleId = $existingArticle?->id;
        $path = $existingArticle?->path;

        if ($articleStatus === ArticleStatus::Unlisted) {
            $path = strtolower(StringUtil::generateRandomString(64));
        } elseif ($articleTitle) {
            $path = strtolower(StringUtil::cleanString($articleTitle));
        } elseif (!$path) {
            $path = strtolower(StringUtil::generateRandomString(64));
        }

        $count = 0;
        do {
            $validPath = $path . ($count > 0 ? '-' . $count : '');
            $res = $this->getArticleForPath($validPath);
            if ($articleId && $res && $res->id === $articleId) {
                $res = null;
            }
            $count++;
        } while(!empty($res));

        $path = $validPath;
        $count = 0;
        do {
            $validPath = $path . ($count > 0 ? '-' . $count : '');
            $res = in_array($validPath, AppRouter::getReservedPaths());
            $count++;
        } while($res);

        return $validPath;
    }

    public function workflowUpdateArticle(
        Article $article,
        array $mediaIds,
        array $media,
        array $sections,
        array $tags,
        ?string $userIp,
        ?string $userAgent,
    ): bool {
        $resTransaction = $this->articleDataLayer->getDb()->withTransaction(
            function () use ($article, $media, $mediaIds, $sections, $tags, $userIp, $userAgent) {
                $resUpdate = $this->articleDataLayer->updateArticle($article);

                if (empty($resUpdate)) {
                    $this->logger->logError('Error updating article. Article ID: ' . $article->id);
                    return new Feedback(false);
                }

                $this->updateCreateOrDeleteArticleMediaRelation(
                    articleId: $article->id,
                    mediaIds: $mediaIds,
                );

                $resSections = $this->updateCreateOrDeleteArticleSections(
                    articleId: $article->id,
                    sections: $sections,
                    media: $media,
                );

                if (empty($resSections)) {
                    $this->logger->logError('Error updating article sections. Article ID: ' . $article->id);
                    return new Feedback(false);
                }

                $resTags = $this->addOrRemoveTagsToArticle($article->id, $tags);
                if (empty($resTags)) {
                    $this->logger->logError('Error updating article tags. Article ID: ' . $article->id);
                    return new Feedback(false);
                }

                $resHistory = $this->articleDataLayer->storeArticleHistory(
                    article: $article,
                    userIp: $userIp,
                    userAgent: $userAgent,
                );

                if (empty($resHistory)) {
                    $this->logger->logError('Error inserting article history. Article ID: ' . $article->id);
                    return new Feedback(false);
                }

                return new Feedback(true);
            }
        );

        return $resTransaction->isSuccess;
    }

    private function updateCreateOrDeleteArticleMediaRelation(int $articleId, array $mediaIds): void
    {
        $existingMedia = $this->getMediaForArticleId($articleId);
        $existingMediaById = [];
        $newMediaById = [];

        foreach ($mediaIds as $mediaId) {
            $newMediaById[$mediaId] = true;
        }

        /** @var Media $media */
        foreach ($existingMedia as $media) {
            $existingMediaById[$media->id] = true;
        }

        foreach ($newMediaById as $mediaId => $ignore) {
            if (empty($existingMediaById[$mediaId])) {
                $this->articleDataLayer->storeArticleMediaRelation($articleId, $mediaId);
            }
        }

        foreach ($existingMediaById as $mediaId => $ignore) {
            if (empty($newMediaById[$mediaId])) {
                $this->articleDataLayer->destroyArticleMediaRelation($articleId, $mediaId);
            }
        }
    }

    private function updateCreateOrDeleteArticleSections(int $articleId, array $sections, array $media): bool
    {
        $existingSections = $this->articleDataLayer->getSectionsForArticleId($articleId);
        $articleSectionsById = [];
        /** @var ArticleSection $existingSection */
        foreach ($existingSections as $existingSection) {
            $articleSectionsById[$existingSection->id] = $existingSection;
        }

        $mediaById = [];
        if ($media) {
            /** @var Media $existingMediaItem */
            foreach ($media as $existingMediaItem) {
                $mediaById[$existingMediaItem->id] = $existingMediaItem;
            }
        }

        $now = new DateTimeImmutable();
        $newSections = [];
        foreach ($sections as $section) {
            $newSectionId = isset($section['id']) ? (int)$section['id'] : null;
            $sectionType = isset($section['sectionTypeId'])
                && ArticleSectionType::tryFrom($section['sectionTypeId'])
                    ? ArticleSectionType::from($section['sectionTypeId'])
                    : ArticleSectionType::TextParagraph;
            $contentHtml = isset($section['contentHtml'])
                ? html_entity_decode($section['contentHtml'])
                : '';
            $contentHtml = StringUtil::sanitiseHtml($contentHtml);
            $mediaCaption = isset($section['imageCaptionHtml'])
                ? StringUtil::sanitiseHtml($section['imageCaptionHtml'])
                : null;
            $mediaForSection = isset($section['mediaId']) ? ($mediaById[$section['mediaId']] ?? null) : null;

            $newSections[] = new ArticleSection(
                id: $newSectionId,
                articleId: $articleId,
                type: $sectionType,
                contentHtml: $contentHtml,
                sequence: isset($section['sequence']) ? (int)$section['sequence'] : null,
                mediaCaption: $mediaCaption,
                createdAt: $newSectionId && isset($articleSectionsById[$newSectionId])
                    ? $articleSectionsById[$newSectionId]->updatedAt
                    : $now,
                updatedAt: $now,
                media: $mediaForSection,
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
        array $media,
        array $tags,
        ?string $userIp,
        ?string $userAgent,
    ): ?Article {
        $resTransaction = $this->articleDataLayer->getDb()->withTransaction(
            function () use ($article, $sections, $media, $tags, $userIp, $userAgent) {
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
                    media: $media,
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

        if ($section->media) {
            $resImage = $this->articleDataLayer->createArticleSectionImage(
                articleSectionId: $section->id,
                imageId: $section->media->id,
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

        if ($section->media) {
            $resImage = $this->articleDataLayer->updateArticleSectionImage(
                articleSectionId: $section->id,
                imageId: $section->media->id,
                imageCaption: $section->mediaCaption,
            );

            if (empty($resImage)) {
                return false;
            }
        }

        return true;
    }

    public function storeArticlePath(ArticlePath $articlePath): ArticlePath
    {
        return $this->articleDataLayer->storeArticlePath($articlePath);
    }

    public function storePageContent(PageContent $pageContent): ?PageContent
    {
        $res = $this->articleDataLayer->getDb()->withTransaction(
            function () use($pageContent) {
                $resOne = $this->articleDataLayer->storePageContent($pageContent);

                if (empty($resOne)) {
                    return new Feedback(false);
                }

                $resTwo = $this->articleDataLayer->storePageContentHistory($pageContent);

                return new Feedback(
                    isSuccess: $resTwo,
                    response: $resOne,
                );
            },
        );

        return $res->isSuccess
            ? $res->response
            : null;
    }

    public function updatePageContent(PageContent $pageContent): bool
    {
        return $this->articleDataLayer->updatePageContent($pageContent);
    }

    public function workflowUpdatePageContent(
        User $user,
        PageContentType|AppPageContentType $contentType,
        array $contentItems,
        ?Collection $collection,
        ?Media $mainImage,
    ): Feedback {
        return $this->articleDataLayer->getDb()->withTransaction(
            function () use ($user, $contentType, $contentItems, $collection, $mainImage) {
                $existingPageContent = $this->filterPageContentBy(
                    typeIds: [$contentType->value],
                );

                if (!$existingPageContent) {
                    return new Feedback(
                        isSuccess: false,
                        message: 'Page content ID not found',
                    );
                }

                $existingPageContentById = [];
                $existingPageContentByIsoCode = [];

                /** @var PageContent $item */
                foreach ($existingPageContent as $item) {
                    $existingPageContentById[$item->id] = $item;
                    $existingPageContentByIsoCode[$item->language->value] = $item;
                }

                foreach ($contentItems as $contentItem) {
                    $languageIsoCode = $contentItem['languageIsoCode'] ?? null;

                    if (!$languageIsoCode || !Language::tryFrom($languageIsoCode)) {
                        continue;
                    }

                    $id = isset($contentItem['id']) ? (int)$contentItem['id'] : null;
                    $title = StringUtil::sanitiseText($contentItem['title'] ?? null);
                    $subtitle = StringUtil::sanitiseText($contentItem['subtitle'] ?? null);
                    $contentHtml = StringUtil::sanitiseHtml($contentItem['contentHtml'] ?? null);
                    $actionUrl = StringUtil::sanitiseText($contentItem['actionUrl'] ?? null);

                    if (!$id && isset($existingPageContentByIsoCode[$languageIsoCode])) {
                        $id = $existingPageContentByIsoCode[$languageIsoCode]->id;
                    }

                    $now = new DateTimeImmutable();
                    $existingPageContent = $id ? ($existingPageContentById[$id] ?? null) : null;

                    $pageContent = new PageContent(
                        id: $id,
                        user: $user,
                        language: Language::from($languageIsoCode),
                        type: $contentType,
                        createdAt: $existingPageContent?->createdAt ?? $now,
                        updatedAt: $now,
                        title: $title,
                        subtitle: $subtitle,
                        contentHtml: $contentHtml,
                        mainImage: $mainImage,
                        actionUrl: $actionUrl,
                        collection: $collection,
                    );

                    $resUpdate = $id
                        ? $this->articleDataLayer->updatePageContent($pageContent)
                        : $this->articleDataLayer->storePageContent($pageContent);

                    if (!$resUpdate) {
                        return new Feedback(
                            isSuccess: false,
                            message: 'Error updating page content',
                        );
                    }

                    $resHistory = $this->articleDataLayer->storePageContentHistory($pageContent);

                    if (empty($resHistory)) {
                        return new Feedback(
                            isSuccess: false,
                            message: 'Error inserting article history. Page content ID: ' . $pageContent->id,
                        );
                    }
                }

                return new Feedback(true);
            }
        );
    }

    public function getFeedItemsForArticles(
        ?ArticleType $articleType = null,
        ?Language $languageIsoCode = null,
        ?int $maxItems = null,
    ): array {
        $articles = $this->filterArticleBy(
            languageIsoCodes: $languageIsoCode ? [$languageIsoCode->value] : [],
            statusIds: [ArticleStatus::Published->value],
            typeIds: $articleType ? [$articleType->value] : [],
            publishedBefore: new DateTimeImmutable(),
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy(field: 'published_at', direction: QueryOrderDirection::DESC)],
                pagination: $maxItems ? new Pagination($maxItems) : null,
            ),
        );

        $feedItems = [];
        /** @var Article $article */
        foreach ($articles as $article) {
            $feedItems[] = new FeedItem(
                uniqueIdentifier: md5($article->id . $article->createdAt->getTimestamp()),
                fullPath: UrlBuilderUtil::buildPublicArticlePath($article->path),
                title: $article->title ?: '',
                contentHtml: $article->contentHtml,
                publishedOn: $article->publishOn,
                language: $article->language,
                user: $article->user,
                media: $article->mainImage,
                updatedAt: $article->updatedAt,
                tags: $article->tags,
            );
        }

        return $feedItems;
    }

    public function getTotalArticles(): array {
        return $this->articleDataLayer->getTotalArticles();
    }
}
