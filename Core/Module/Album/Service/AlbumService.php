<?php

namespace Amora\Core\Module\Album\Service;

use Amora\App\Value\Language;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\Album\Datalayer\AlbumDataLayer;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Model\Collection;
use Amora\Core\Module\Album\Model\CollectionMedia;
use Amora\Core\Module\Album\Model\AlbumSlug;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Module\Album\Value\Template;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\StringUtil;
use DateTimeImmutable;

readonly class AlbumService
{
    public function __construct(
        private AlbumDataLayer $albumDataLayer,
    ) {}

    public function getAlbumForId(
        int $id,
        ?Language $language = null,
        bool $includeCollections = false,
        bool $includeMedia = false,
    ): ?Album {
        $res = $this->filterAlbumBy(
            albumIds: [$id],
            languageIsoCodes: $language ? [$language->value] : [],
            includeCollections: $includeCollections,
            includeMedia: $includeMedia,
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getCollectionForId(
        int $collectionId,
        bool $includeMedia = false,
    ): ?Collection {
        $res = $this->filterCollectionBy(
            collectionIds: [$collectionId],
            includeMedia: $includeMedia,
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getCollectionMediaForId(int $collectionMediaId): ?CollectionMedia
    {
        $res = $this->filterCollectionMediaBy(
            collectionMediaIds: [$collectionMediaId],
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getCollectionMediaForMediaIds(int $collectionId, int $mediaId): ?CollectionMedia
    {
        $res = $this->filterCollectionMediaBy(
            collectionIds: [$collectionId],
            mediaIds: [$mediaId],
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getAlbumForSlug(
        string $slug,
        bool $includeCollections = false,
        bool $includeMedia = false,
    ): ?Album {
        $res = $this->filterAlbumBy(
            slug: $slug,
            includeCollections: $includeCollections,
            includeMedia: $includeMedia,
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function filterAlbumBy(
        array $albumIds = [],
        array $languageIsoCodes = [],
        array $statusIds = [],
        array $templateIds = [],
        array $mediaIds = [],
        ?string $slug = null,
        ?string $searchQuery = null,
        bool $includeCollections = false,
        bool $includeMedia = false,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->albumDataLayer->filterAlbumBy(
            albumIds: $albumIds,
            languageIsoCodes: $languageIsoCodes,
            statusIds: $statusIds,
            templateIds: $templateIds,
            mediaIds: $mediaIds,
            slug: $slug,
            searchQuery: $searchQuery,
            includeCollections: $includeCollections,
            includeMedia: $includeMedia,
            queryOptions: $queryOptions,
        );
    }

    public function filterCollectionBy(
        array $collectionIds = [],
        array $albumIds = [],
        array $mediaIds = [],
        ?string $searchQuery = null,
        bool $includeMedia = false,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->albumDataLayer->filterCollectionBy(
            collectionIds: $collectionIds,
            albumIds: $albumIds,
            mediaIds: $mediaIds,
            searchQuery: $searchQuery,
            includeMedia: $includeMedia,
            queryOptions: $queryOptions,
        );
    }

    public function filterCollectionMediaBy(
        array $collectionMediaIds = [],
        array $collectionIds = [],
        array $mediaIds = [],
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->albumDataLayer->filterCollectionMediaBy(
            collectionMediaIds: $collectionMediaIds,
            collectionIds: $collectionIds,
            mediaIds: $mediaIds,
            queryOptions: $queryOptions,
        );
    }

    public function getAlbumSlugForSlug(string $slug): ?AlbumSlug
    {
        $res = $this->albumDataLayer->filterAlbumSlugBy(
            slug: $slug,
        );

        return $res[0] ?? null;
    }

    public function workflowStoreAlbum(
        Language $language,
        Template $template,
        User $user,
        Media $mainMedia,
        string $titleHtml,
        ?string $contentHtml,
    ): Album {
        $resTransaction = $this->albumDataLayer->getDb()->withTransaction(
            function () use (
                $language,
                $template,
                $user,
                $mainMedia,
                $titleHtml,
                $contentHtml,
            ) {
                $now = new DateTimeImmutable();
                $slug = $this->getOrStoreAvailableSlugForAlbum(title: $titleHtml);
                $resStore = $this->albumDataLayer->storeAlbum(
                    new Album(
                        id: null,
                        language: $language,
                        user: $user,
                        status: AlbumStatus::Draft,
                        mainMedia: $mainMedia,
                        template: $template,
                        slug: $slug,
                        createdAt: $now,
                        updatedAt: $now,
                        titleHtml: $titleHtml,
                        contentHtml: $contentHtml,
                    ),
                );

                $resSlug = $this->albumDataLayer->updateAlbumSlugRelation(
                    slugId: $resStore->slug->id,
                    albumId: $resStore->id,
                );

                if (!$resSlug) {
                    return new Feedback(
                        isSuccess: true,
                        message: 'Error updating album/slug relation',
                    );
                }

                return new Feedback(
                    isSuccess: true,
                    response: $resStore,
                );
            }
        );

        return $resTransaction->response;
    }

    public function workflowUpdateAlbum(
        Album $existingAlbum,
        Language $language,
        Template $template,
        Media $mainMedia,
        string $titleHtml,
        ?string $contentHtml,
    ): Album {
        $resTransaction = $this->albumDataLayer->getDb()->withTransaction(
            function () use (
                $existingAlbum,
                $language,
                $template,
                $mainMedia,
                $titleHtml,
                $contentHtml,
            ) {
                $now = new DateTimeImmutable();
                $newAlbum = new Album(
                    id: $existingAlbum->id,
                    language: $language,
                    user: $existingAlbum->user,
                    status: $existingAlbum->status,
                    mainMedia: $mainMedia,
                    template: $template,
                    slug: $this->getOrStoreAvailableSlugForAlbum(
                        title: $titleHtml,
                        existingSlug: $existingAlbum->slug,
                        albumId: $existingAlbum->id,
                    ),
                    createdAt: $existingAlbum->createdAt,
                    updatedAt: $now,
                    titleHtml: $titleHtml,
                    contentHtml: $contentHtml,
                );

                $resStore = $this->albumDataLayer->updateAlbum($newAlbum);
                if (!$resStore) {
                    return new Feedback(
                        isSuccess: true,
                        message: 'Error updating album. ID: ' . $newAlbum->id,
                    );
                }

                $resSlug = $this->albumDataLayer->updateAlbumSlugRelation(
                    slugId: $newAlbum->slug->id,
                    albumId: $newAlbum->id,
                );

                if (!$resSlug) {
                    return new Feedback(
                        isSuccess: true,
                        message: 'Error updating album/slug relation',
                    );
                }

                return new Feedback(
                    isSuccess: true,
                    response: $newAlbum,
                );
            }
        );

        return $resTransaction->response;
    }

    public function getOrStoreAvailableSlugForAlbum(
        ?string $title = null,
        ?AlbumSlug $existingSlug = null,
        ?int $albumId = null,
    ): AlbumSlug {
        $slug = StringUtil::generateSlug($title);

        $count = 0;
        do {
            $validSlug = $slug . ($count > 0 ? '-' . $count : '');
            $existingAlbum = $this->getAlbumForSlug($validSlug);
            if ($existingSlug && $albumId && $existingAlbum->slug->albumId === $albumId) {
                return $existingAlbum->slug;
            }

            if ($existingSlug && $existingAlbum && $existingAlbum->id === $existingSlug->albumId) {
                return $existingSlug;
            }
            $count++;
        } while(!empty($existingAlbum));

        return $this->albumDataLayer->storeAlbumSlug(
            new AlbumSlug(
                id: null,
                albumId: $albumId,
                slug: $validSlug,
                createdAt: new DateTimeImmutable(),
            ),
        );
    }

    public function workflowUpdateAlbumStatus(int $albumId, AlbumStatus $newStatus): bool
    {
        return $this->albumDataLayer->updateAlbumFields(
            albumId: $albumId,
            newStatus: $newStatus,
        );
    }

    public function updateMediaSequenceForCollection(
        CollectionMedia $collectionMediaFrom,
        CollectionMedia $collectionMediaTo,
    ): bool {
        return $this->albumDataLayer->updateMediaSequenceForCollection(
            collectionMediaFrom: $collectionMediaFrom,
            collectionMediaTo:  $collectionMediaTo,
        );
    }

    public function storeCollection(Collection $collection): Collection
    {
        return $this->albumDataLayer->storeCollection($collection);
    }

    public function workflowStoreCollection(
        Album $album,
        ?Media $mainMedia,
        ?string $titleHtml,
        ?string $subtitleHtml,
        ?string $contentHtml,
    ): Collection {
        $resTransaction = $this->albumDataLayer->getDb()->withTransaction(
            function () use (
                $album,
                $mainMedia,
                $titleHtml,
                $subtitleHtml,
                $contentHtml,
            ) {
                $sequence = $this->albumDataLayer->getMaxCollectionSequence($album->id);

                $now = new DateTimeImmutable();
                $resStore = $this->albumDataLayer->storeCollection(
                    new Collection(
                        id: null,
                        albumId: $album->id,
                        mainMedia: $mainMedia,
                        titleHtml: $titleHtml,
                        subtitleHtml: $subtitleHtml,
                        contentHtml: $contentHtml,
                        createdAt: $now,
                        updatedAt: $now,
                        sequence: isset($sequence) ? $sequence + 1 : 0,
                    ),
                );

                return new Feedback(
                    isSuccess: true,
                    response: $resStore,
                );
            }
        );

        return $resTransaction->response;
    }

    public function updateCollection(Collection $item): bool
    {
        return $this->albumDataLayer->updateCollection($item);
    }

    public function setMainMediaIdForCollection(int $collectionId, int $mainMediaId): bool
    {
        return $this->albumDataLayer->updateCollectionFields(
            collectionId: $collectionId,
            mainMediaId: $mainMediaId,
        );
    }

    public function workflowUpdateCollection(
        Collection  $collectionFrom,
        ?Collection $collectionTo,
        Collection  $updated,
    ): bool {
        $resTransaction = $this->albumDataLayer->getDb()->withTransaction(
            function () use ($collectionFrom, $collectionTo, $updated)
            {
                if ($collectionTo && $collectionFrom->sequence !== $collectionTo->sequence) {
                    $resSequence = $this->albumDataLayer->updateCollectionSequenceForAlbum(
                        collectionFrom: $collectionFrom,
                        collectionTo: $collectionTo,
                    );

                    if (!$resSequence) {
                        return new Feedback(false);
                    }
                }

                $res = $this->albumDataLayer->updateCollection($updated);

                return new Feedback($res);
            }
        );

        return $resTransaction->isSuccess;
    }

    public function updateCollectionMedia(CollectionMedia $item): bool
    {
        return $this->albumDataLayer->updateCollectionMedia($item);
    }

    public function workflowDeleteMediaForCollection(CollectionMedia $collectionMedia): bool
    {
        $resTransaction = $this->albumDataLayer->getDb()->withTransaction(
            function () use ($collectionMedia)
            {
                $this->albumDataLayer->updateCollectionSequenceWhenMediaIsDeletedForAlbum($collectionMedia);

                $res = $this->albumDataLayer->deleteMediaForCollection($collectionMedia->id);

                return new Feedback(
                    isSuccess: $res,
                );
            }
        );

        return $resTransaction->isSuccess;
    }

    public function workflowCreateCollectionAndStoreMedia(
        Media $media,
        bool $isMainMedia,
        ?string $titleHtml = null,
        ?string $subtitleHtml = null,
        ?string $contentHtml = null,
        ?string $mediaCaptionHtml = null,
    ): array {
        $resTransaction = $this->albumDataLayer->getDb()->withTransaction(
            function () use (
                $media,
                $isMainMedia,
                $titleHtml,
                $subtitleHtml,
                $contentHtml,
                $mediaCaptionHtml,
            ) {
                $now = new DateTimeImmutable();
                $newCollection = $this->storeCollection(
                    new Collection(
                        id: null,
                        albumId: null,
                        mainMedia: $isMainMedia ? $media : null,
                        titleHtml: null,
                        subtitleHtml: $subtitleHtml,
                        contentHtml: $contentHtml,
                        createdAt: $now,
                        updatedAt: $now,
                        sequence: 0,
                    ),
                );

                $newCollectionMedia = null;
                if (!$isMainMedia) {
                    $newCollectionMedia = $this->storeMediaForCollection(
                        collection: $newCollection,
                        media: $media,
                        captionHtml: $mediaCaptionHtml,
                    );
                }

                return new Feedback(
                    isSuccess: true,
                    response: [
                        'collection' => $newCollection,
                        'collectionMedia' => $newCollectionMedia,
                    ],
                );
            }
        );

        return $resTransaction->response;
    }

    public function storeMediaForCollection(
        Collection $collection,
        Media $media,
        ?string $captionHtml,
    ): CollectionMedia {
        $sequence = $this->albumDataLayer->getMaxCollectionMediaSequence($collection->id);

        $now = new DateTimeImmutable();
        return $this->albumDataLayer->storeCollectionMedia(
            new CollectionMedia(
                id: null,
                collectionId: $collection->id,
                media: $media,
                captionHtml: $captionHtml,
                createdAt: $now,
                updatedAt: $now,
                sequence: $sequence + 1,
            ),
        );
    }

    public function getTotalAlbums(): int {
        return $this->albumDataLayer->getTotalAlbums();
    }
}
