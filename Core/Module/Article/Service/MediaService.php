<?php

namespace Amora\Core\Module\Article\Service;

use Amora\App\Value\Language;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Response;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Service\AlbumService;
use Amora\Core\Module\Article\Entity\RawFile;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\ImageExif;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Model\MediaDestroyed;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\Logger;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\Article\DataLayer\MediaDataLayer;
use Amora\Core\Util\StringUtil;
use Amora\Core\Value\QueryOrderDirection;
use DateTimeImmutable;
use Throwable;

readonly class MediaService
{
    public const int FILE_PERMISSIONS = 0755;
    private const int FOLDER_PERMISSIONS = 0755;

    public function __construct(
        private Logger $logger,
        private ArticleService $articleService,
        private MediaDataLayer $mediaDataLayer,
        private ImageService $imageService,
        private AlbumService $albumService,
        private string $mediaBaseDir,
    ) {
    }

    public function storeMedia(Media $item): Media
    {
        return $this->mediaDataLayer->storeMedia($item);
    }

    public function storeMediaExif(int $mediaId, ?ImageExif $exif): void
    {
        $this->mediaDataLayer->storeMediaExif($mediaId, $exif);
    }

    public function updateMedia(Media $item): bool
    {
        return $this->mediaDataLayer->updateMedia($item);
    }

    public function updateMediaFields(
        int $mediaId,
        ?string $caption = null,
        ?DateTimeImmutable $uploadedToS3At = null,
        ?DateTimeImmutable $deletedLocallyAt = null,
    ): bool {
        return $this->mediaDataLayer->updateMediaFields(
            mediaId: $mediaId,
            caption: $caption,
            uploadedToS3At: $uploadedToS3At,
            deletedLocallyAt: $deletedLocallyAt,
        );
    }

    public function storeMediaDestroyed(MediaDestroyed $item): MediaDestroyed
    {
        return $this->mediaDataLayer->storeMediaDestroyed($item);
    }

    public function getMediaForId(int $id): ?Media
    {
        $res = $this->filterMediaBy(
            ids: [$id],
            statusIds: [MediaStatus::Active->value],
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function filterMediaBy(
        array $ids = [],
        array $userIds = [],
        array $typeIds = [],
        array $statusIds = [],
        ?int $fromId = null,
        ?DateTimeImmutable $uploadedToS3Before = null,
        ?bool $isUploadedToS3 = null,
        ?bool $isDeletedLocally = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->mediaDataLayer->filterMediaBy(
            ids: $ids,
            userIds: $userIds,
            typeIds: $typeIds,
            statusIds: $statusIds,
            fromId: $fromId,
            uploadedToS3Before: $uploadedToS3Before,
            isUploadedToS3: $isUploadedToS3,
            isDeletedLocally: $isDeletedLocally,
            queryOptions: $queryOptions,
        );
    }

    public function markMediaAsDeleted(Media $media): bool
    {
        $res = $this->mediaDataLayer->markMediaAsDeleted($media->id);
        if (empty($res)) {
            $this->logger->logError('Error deleting image. Image ID: ' . $media->id);
            return false;
        }

        return true;
    }

    public function destroyMedia(Media $media): bool
    {
        if (file_exists($media->getDirWithNameOriginal()) && !unlink($media->getDirWithNameOriginal())) {
            return false;
        }

        if (
            $media->filenameXSmall &&
            file_exists($media->getDirWithNameXSmall()) &&
            !unlink($media->getDirWithNameXSmall())
        ) {
            return false;
        }

        if (
            $media->filenameSmall &&
            file_exists($media->getDirWithNameSmall()) &&
            !unlink($media->getDirWithNameSmall())
        ) {
            return false;
        }

        if (
            $media->filenameMedium &&
            file_exists($media->getDirWithNameMedium()) &&
            !unlink($media->getDirWithNameMedium())
        ) {
            return false;
        }

        if (
            $media->filenameLarge &&
            file_exists($media->getDirWithNameLarge()) &&
            !unlink($media->getDirWithNameLarge())
        ) {
            return false;
        }

        if (
            $media->filenameXLarge &&
            file_exists($media->getDirWithNameXLarge()) &&
            !unlink($media->getDirWithNameXLarge())
        ) {
            return false;
        }

        return $this->mediaDataLayer->destroyMedia($media->id);
    }

    public function workflowGetFiles(
        Language $language,
        QueryOrderDirection $direction,
        int $qty,
        ?MediaType $mediaType = null,
        bool $isAdmin = false,
        bool $includeAppearsOn = false,
        ?int $mediaId = null,
        ?int $fromId = null,
        ?int $userId = null,
        bool $includeExifData = false,
    ): array {
        $files = QueryOrderDirection::RAND === $direction
            ? $this->filterMediaBy(
                ids: $mediaId ? [$mediaId] : [],
                userIds: $userId ? [$userId] : [],
                typeIds: $mediaType ? [$mediaType->value] : [],
                statusIds: [MediaStatus::Active->value],
                queryOptions: new QueryOptions(
                    pagination: new Response\Pagination(itemsPerPage: $qty),
                    orderRandomly: true,
                ),
            )
            : $this->filterMediaBy(
                ids: $mediaId ? [$mediaId] : [],
                userIds: $userId ? [$userId] : [],
                typeIds: $mediaType ? [$mediaType->value] : [],
                statusIds: [MediaStatus::Active->value],
                fromId: $fromId,
                queryOptions: new QueryOptions(
                    orderBy: [new QueryOrderBy('id', $direction)],
                    pagination: new Response\Pagination(itemsPerPage: $qty),
                ),
            );

        $output = [];
        /** @var Media $file */
        foreach ($files as $file) {
            $fileOutput = $file->asPublicArray();

            if ($includeAppearsOn) {
                $statusIds = $isAdmin
                    ? [ArticleStatus::Published->value, ArticleStatus::Unlisted->value, ArticleStatus::Private->value]
                    : [ArticleStatus::Published->value];

                $articles = $this->articleService->filterArticleBy(
                    statusIds: $statusIds,
                    imageIds: [$file->id],
                );

                $albums = $this->albumService->filterAlbumBy(
                    mediaIds: [$file->id],
                );

                $appearsOn = [];
                /** @var Article $article */
                foreach ($articles as $article) {
                    $appearsOn[] = $article->asPublicArray();
                }

                /** @var Album $album */
                foreach ($albums as $album) {
                    $appearsOn[] = $album->asPublicArray();
                }

                $fileOutput['appearsOn'] = $appearsOn;

                if ($includeExifData && $file->type === MediaType::Image) {
                    $fileOutput['exifHtml'] = $file->exif?->asHtml(
                        language: $language,
                        media: $file,
                    ) ?? '';
                }
            }

            $output[] = $fileOutput;
        }

        return $output;
    }

    public function workflowStoreFile(
        array $rawFiles,
        ?User $user,
    ): Feedback {
        return $this->mediaDataLayer->getDb()->withTransaction(
            function () use (
                $rawFiles,
                $user,
            ) {
                try {
                    $rawFile = $this->validateAndProcessRawFile($rawFiles);
                    if (!$rawFile) {
                        return new Feedback(
                            isSuccess: false,
                            message: 'Raw file not valid',
                        );
                    }

                    $processedMedia = match ($rawFile->mediaType) {
                        MediaType::Image => $this->processRawFileImage($rawFile, $user),
                        default => $this->processRawFile($rawFile, $user),
                    };

                    if (!$processedMedia) {
                        return new Feedback(
                            isSuccess: false,
                            message: 'File not valid',
                        );
                    }

                    $output = $this->storeMedia($processedMedia);

                    return new Feedback(
                        isSuccess: true,
                        response: $output,
                    );
                } catch (Throwable $t) {
                    $this->logger->logError(
                        'Error storing file: '
                        . $t->getMessage()
                        . ' - Trace: ' . $t->getTraceAsString()
                    );

                    return new Feedback(
                        isSuccess: false,
                        message: 'Error storing file: ' . $t->getMessage(),
                    );
                }
            }
        );
    }

    public function processRawFileImage(
        RawFile $rawFile,
        ?User $user,
        ?string $captionHtml = null,
        bool $keepSameImageFormat = false,
    ): ?Media {
        try {
            return $this->imageService->resizeRawImage(
                rawFile: $rawFile,
                user: $user,
                captionHtml: $captionHtml,
                keepSameImageFormat: $keepSameImageFormat,
            );
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error processing image: '
                . $t->getMessage()
                . ' - Trace: ' . $t->getTraceAsString()
            );

            return null;
        }
    }

    public function processRawFile(RawFile $rawFile, ?User $user): Media
    {
        $now = new DateTimeImmutable();
        return new Media(
            id: null,
            type: $rawFile->mediaType,
            status: MediaStatus::Active,
            user: $user,
            widthOriginal: null,
            heightOriginal: null,
            path: $rawFile->extraPath,
            filename: $rawFile->getName(),
            filenameSource: $rawFile->originalName,
            filenameXLarge: null,
            filenameLarge: null,
            filenameMedium: null,
            filenameSmall: null,
            filenameXSmall: null,
            captionHtml: null,
            createdAt: $now,
            updatedAt: $now,
            uploadedToS3At: null,
            deletedLocallyAt: null,
            exif: null,
        );
    }

    public function validateAndProcessRawFile(array $rawFiles): ?RawFile
    {
        if (empty($rawFiles['files']['name'][0])) {
            $this->logger->logError('Raw file name is empty');
            return null;
        }

        if (empty($rawFiles['files']['tmp_name'][0])) {
            $this->logger->logError('Raw file tmp_name is empty');
            return null;
        }

        $rawPathWithName = $rawFiles['files']['tmp_name'][0];
        if (!file_exists($rawPathWithName)) {
            $this->logger->logError('File not not found: ' . $rawPathWithName);
            return null;
        }

        $mediaType = MediaType::getTypeFromRawFileType($rawFiles['files']['type'][0]);
        $rawName = $rawFiles['files']['name'][0];
        $baseNameWithoutExtension = StringUtil::generateRandomString(32);

        if ($mediaType === MediaType::Image) {
            $phpNativeImageType = exif_imagetype($rawPathWithName);
            $extension = $this->imageService->getExtension($phpNativeImageType);
        } else {
            $extension = $this->getFileExtension($rawName);
            $rawNameWithoutExtension = trim(str_replace($extension, '', $rawName), '. ');
            $baseNameWithoutExtension = StringUtil::cleanString($rawNameWithoutExtension) .
                '-' .
                $baseNameWithoutExtension;
        }

        $basePath = rtrim($this->mediaBaseDir, ' /');
        $extraPath = $this->getOrGenerateMediaFolder($basePath);
        if (!$extraPath) {
            return null;
        }

        $targetPath = $basePath . '/' . $extraPath . '/' . $baseNameWithoutExtension . '.' . $extension;

        $res = @rename($rawPathWithName, $targetPath);
        if (!$res) {
            $this->logger->logError(
                'Error renaming file from ' . $rawPathWithName . ' to ' . $targetPath
            );
            return null;
        }

        $resP = chmod($targetPath, self::FILE_PERMISSIONS);
        if (!$resP) {
            $this->logger->logError('Error updating file permissions: ' . $rawPathWithName);
        }

        return new RawFile(
            originalName: $rawName,
            baseNameWithoutExtension: $baseNameWithoutExtension,
            extension: $extension,
            basePath: $basePath,
            extraPath: $extraPath,
            mediaType: $mediaType,
            sizeBytes: (int)$rawFiles['files']['size'][0],
            error: $rawFiles['files']['error'][0],
        );
    }

    private function getFileExtension(string $filename): string
    {
        if (!str_contains($filename, '.')) {
            return '';
        }

        $parts = explode('.', $filename);
        return strtolower(trim($parts[count($parts) - 1]));
    }

    public function getOrGenerateMediaFolder(string $mediaBasePath): ?string
    {
        if (
            !is_dir($mediaBasePath) &&
            !mkdir($mediaBasePath, self::FOLDER_PERMISSIONS, true) &&
            !is_dir($mediaBasePath)
        ) {
            $this->logger->logError('Failed to create folder: ' . $mediaBasePath);
            return null;
        }

        $now = new DateTimeImmutable();
        $mediaExtraPath = md5($now->format('Y-W'));
        $fullPath = $mediaBasePath . '/' . $mediaExtraPath;

        if (is_dir($fullPath)) {
            return $mediaExtraPath;
        }

        $count = 0;
        do {
            if ($count) {
                $mediaExtraPath .= $count;
            }

            $fullPath = $mediaBasePath . '/' . $mediaExtraPath;
            if (!mkdir($fullPath, self::FOLDER_PERMISSIONS, true) && !is_dir($fullPath)) {
                $this->logger->logError('Failed to create folder: ' . $fullPath);
                return null;
            }

            $count++;
        } while (!is_dir($fullPath));

        chmod($fullPath, self::FOLDER_PERMISSIONS);

        return $mediaExtraPath;
    }

    public function getMediaCountByTypeId(): array
    {
        return $this->mediaDataLayer->getMediaCountByTypeId();
    }
}
