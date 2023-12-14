<?php

namespace Amora\Core\Module\Album\Service;

use Amora\App\Value\Language;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\Album\Datalayer\AlbumDataLayer;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Model\AlbumSection;
use Amora\Core\Module\Album\Model\AlbumSectionMedia;
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
        bool $includeSections = false,
        bool $includeMedia = false,
    ): ?Album {
        $res = $this->filterAlbumBy(
            albumIds: [$id],
            languageIsoCodes: $language ? [$language->value] : [],
            includeSections: $includeSections,
            includeMedia: $includeMedia,
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getAlbumSectionForId(
        int $albumSectionId,
        bool $includeMedia = false,
    ): ?AlbumSection {
        $res = $this->filterAlbumSectionBy(
            albumSectionIds: [$albumSectionId],
            includeMedia: $includeMedia,
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getAlbumSectionMediaForId(int $albumSectionMediaId): ?AlbumSectionMedia
    {
        $res = $this->filterAlbumSectionMediaBy(
            albumSectionMediaIds: [$albumSectionMediaId],
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getAlbumSectionMediaForIds(int $albumSectionId, int $mediaId): ?AlbumSectionMedia
    {
        $res = $this->filterAlbumSectionMediaBy(
            albumSectionIds: [$albumSectionId],
            mediaIds: [$mediaId],
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getAlbumForSlug(
        string $slug,
        bool $includeSections = false,
        bool $includeMedia = false,
    ): ?Album {
        $res = $this->filterAlbumBy(
            slug: $slug,
            includeSections: $includeSections,
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
        bool $includeSections = false,
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
            includeSections: $includeSections,
            includeMedia: $includeMedia,
            queryOptions: $queryOptions,
        );
    }

    public function filterAlbumSectionBy(
        array $albumSectionIds = [],
        array $albumIds = [],
        array $mediaIds = [],
        bool $includeMedia = false,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->albumDataLayer->filterAlbumSectionBy(
            albumSectionIds: $albumSectionIds,
            albumIds: $albumIds,
            mediaIds: $mediaIds,
            includeMedia: $includeMedia,
            queryOptions: $queryOptions,
        );
    }

    public function filterAlbumSectionMediaBy(
        array $albumSectionMediaIds = [],
        array $albumSectionIds = [],
        array $mediaIds = [],
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->albumDataLayer->filterAlbumSectionMediaBy(
            albumSectionMediaIds: $albumSectionMediaIds,
            albumSectionIds: $albumSectionIds,
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
        ?int $eventId = null,
    ): AlbumSlug {
        $slug = StringUtil::generateSlug($title);

        $count = 0;
        do {
            $validSlug = $slug . ($count > 0 ? '-' . $count : '');
            $res = $this->getAlbumForSlug($validSlug);
            if ($existingSlug && $res && $res->id === $existingSlug->id) {
                return $existingSlug;
            }
            $count++;
        } while(!empty($res));

        return $this->albumDataLayer->storeAlbumSlug(
            new AlbumSlug(
                id: null,
                albumId: $eventId,
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

    public function updateMediaSequenceForAlbumSection(
        AlbumSectionMedia $albumSectionMediaFrom,
        AlbumSectionMedia $albumSectionMediaTo,
    ): bool {
        return $this->albumDataLayer->updateMediaSequenceForAlbumSection(
            albumSectionMediaFrom: $albumSectionMediaFrom,
            albumSectionMediaTo:  $albumSectionMediaTo,
        );
    }

    public function workflowStoreAlbumSection(
        Album $album,
        ?Media $mainMedia,
        ?string $titleHtml,
        ?string $subtitleHtml,
        ?string $contentHtml,
    ): AlbumSection {
        $resTransaction = $this->albumDataLayer->getDb()->withTransaction(
            function () use (
                $album,
                $mainMedia,
                $titleHtml,
                $subtitleHtml,
                $contentHtml,
            ) {
                $sequence = $this->albumDataLayer->getMaxAlbumSectionSequence($album->id);

                $now = new DateTimeImmutable();
                $resStore = $this->albumDataLayer->storeAlbumSection(
                    new AlbumSection(
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

    public function workflowUpdateAlbumSection(
        AlbumSection $albumSectionFrom,
        ?AlbumSection $albumSectionTo,
        AlbumSection $updated,
    ): bool {
        $resTransaction = $this->albumDataLayer->getDb()->withTransaction(
            function () use ($albumSectionFrom, $albumSectionTo, $updated)
            {
                if ($albumSectionTo && $albumSectionFrom->sequence !== $albumSectionTo->sequence) {
                    $resSequence = $this->albumDataLayer->updateSectionSequenceForAlbum(
                        albumSectionFrom: $albumSectionFrom,
                        albumSectionTo: $albumSectionTo,
                    );

                    if (!$resSequence) {
                        return new Feedback(false);
                    }
                }

                $res = $this->albumDataLayer->updateAlbumSection($updated);

                return new Feedback($res);
            }
        );

        return $resTransaction->isSuccess;
    }

    public function updateAlbumSectionMedia(AlbumSectionMedia $item): bool
    {
        return $this->albumDataLayer->updateAlbumSectionMedia($item);
    }

    public function deleteMediaForAlbumSection(int $albumSectionMediaId): bool
    {
        return $this->albumDataLayer->deleteMediaForAlbumSection($albumSectionMediaId);
    }

    public function workflowStoreMediaForAlbumSection(
        AlbumSection $albumSection,
        Media $media,
        ?string $captionHtml,
    ): AlbumSectionMedia {
        $resTransaction = $this->albumDataLayer->getDb()->withTransaction(
            function () use (
                $albumSection,
                $media,
                $captionHtml,
            ) {
                $sequence = $this->albumDataLayer->getMaxAlbumSectionMediaSequence($albumSection->id);

                $now = new DateTimeImmutable();
                $resStore = $this->albumDataLayer->storeAlbumSectionMedia(
                    new AlbumSectionMedia(
                        id: null,
                        albumSectionId: $albumSection->id,
                        media: $media,
                        captionHtml: $captionHtml,
                        createdAt: $now,
                        updatedAt: $now,
                        sequence: $sequence + 1,
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

    public function getTotalAlbums(): int {
        return $this->albumDataLayer->getTotalAlbums();
    }
}
