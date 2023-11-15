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
    ): ?Album {
        $res = $this->filterAlbumBy(
            albumIds: [$id],
            languageIsoCodes: $language ? [$language->value] : [],
            includeSections: $includeSections,
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getAlbumSectionForId(
        int $id,
        bool $includeMedia = false,
    ): ?AlbumSection {
        $res = $this->filterAlbumSectionBy(
            albumIds: [$id],
            includeMedia: $includeMedia,
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getAlbumForSlug(string $slug): ?Album
    {
        $res = $this->filterAlbumBy(
            slug: $slug,
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function filterAlbumBy(
        array $albumIds = [],
        array $languageIsoCodes = [],
        array $statusIds = [],
        array $templateIds = [],
        ?string $slug = null,
        bool $includeSections = false,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->albumDataLayer->filterAlbumBy(
            albumIds: $albumIds,
            languageIsoCodes: $languageIsoCodes,
            statusIds: $statusIds,
            templateIds: $templateIds,
            slug: $slug,
            includeSections: $includeSections,
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
                $resStore = $this->albumDataLayer->storeAlbum(
                    new Album(
                        id: null,
                        language: $language,
                        user: $user,
                        status: AlbumStatus::Draft,
                        mainMedia: $mainMedia,
                        template: $template,
                        slug: $this->getOrStoreAvailableSlugForAlbum(
                            title: $titleHtml,
                        ),
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

    public function workflowStoreAlbumSection(
        Album $album,
        Media $mainMedia,
        string $titleHtml,
        ?string $contentHtml,
    ): AlbumSection {
        $resTransaction = $this->albumDataLayer->getDb()->withTransaction(
            function () use (
                $album,
                $mainMedia,
                $titleHtml,
                $contentHtml,
            ) {
                $now = new DateTimeImmutable();
                $resStore = $this->albumDataLayer->storeAlbumSection(
                    new AlbumSection(
                        id: null,
                        albumId: $album->id,
                        mainMedia: $mainMedia,
                        titleHtml: $titleHtml,
                        contentHtml: $contentHtml,
                        createdAt: $now,
                        updatedAt: $now,
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

    public function workflowStoreMediaForAlbumSection(
        AlbumSection $albumSection,
        Media $media,
        ?string $titleHtml,
        ?string $contentHtml,
    ): AlbumSectionMedia {
        $resTransaction = $this->albumDataLayer->getDb()->withTransaction(
            function () use (
                $albumSection,
                $media,
                $titleHtml,
                $contentHtml,
            ) {
                $now = new DateTimeImmutable();
                $resStore = $this->albumDataLayer->storeMediaForAlbumSection(
                    new AlbumSectionMedia(
                        id: null,
                        albumSectionId: $albumSection->id,
                        mainMedia: $media,
                        titleHtml: $titleHtml,
                        contentHtml: $contentHtml,
                        createdAt: $now,
                        updatedAt: $now,
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
