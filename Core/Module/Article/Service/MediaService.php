<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Response;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Service\AlbumService;
use Amora\Core\Module\Article\Entity\RawFile;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\Media;
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
    public function __construct(
        private Logger $logger,
        private ArticleService $articleService,
        private MediaDataLayer $mediaDataLayer,
        private ImageService $imageService,
        private AlbumService $albumService,
        private string $mediaBaseDir,
    ) {}

    public function storeMedia(Media $item): Media
    {
        return $this->mediaDataLayer->storeFile($item);
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
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->mediaDataLayer->filterMediaBy(
            ids: $ids,
            userIds: $userIds,
            typeIds: $typeIds,
            statusIds: $statusIds,
            fromId: $fromId,
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
        if (file_exists($media->getDirWithNameOriginal())) {
            if (!unlink($media->getDirWithNameOriginal())) {
                return false;
            }
        }

        if ($media->filenameSmall) {
            if (file_exists($media->getDirWithNameSmall())) {
                if (!unlink($media->getDirWithNameSmall())) {
                    return false;
                }
            }
        }

        if ($media->filenameMedium) {
            if (file_exists($media->getDirWithNameMedium())) {
                if (!unlink($media->getDirWithNameMedium())) {
                    return false;
                }
            }
        }

        if ($media->filenameLarge) {
            if (file_exists($media->getDirWithNameLarge())) {
                if (!unlink($media->getDirWithNameLarge())) {
                    return false;
                }
            }
        }

        return true;
    }

    public function workflowGetFiles(
        QueryOrderDirection $direction,
        int $qty,
        ?MediaType $mediaType = null,
        bool $isAdmin = false,
        bool $includeAppearsOn = false,
        ?int $mediaId = null,
        ?int $fromId = null,
        ?int $userId = null,
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
            $fileOutput = $file->buildPublicDataArray();

            if ($includeAppearsOn) {
                $statusIds = $isAdmin
                    ? [ArticleStatus::Published->value, ArticleStatus::Unlisted->value, ArticleStatus::Private->value]
                    : [ArticleStatus::Published->value];

                $articles = $this->articleService->filterArticlesBy(
                    statusIds: $statusIds,
                    imageIds: [$file->id],
                );

                $albums = $this->albumService->filterAlbumBy(
                    mediaIds: [$file->id],
                );

                $appearsOn = [];
                /** @var Article $article */
                foreach ($articles as $article) {
                    $appearsOn[] = $article->buildPublicDataArray();
                }

                /** @var Album $album */
                foreach ($albums as $album) {
                    $appearsOn[] = $album->buildPublicDataArray();
                }

                $fileOutput['appearsOn'] = $appearsOn;

                if ($file->type === MediaType::Image) {
                    $extension = $this->getFileExtension($file->getPathWithNameOriginal());
                    $exif = $this->imageService->getExifData(
                        filePathWithName: $file->getDirWithNameOriginal(),
                        extension: $extension,
                    );

                    if ($exif) {
                        $fileOutput['exif'] = $exif->asArray();
                    }
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
                    if (empty($rawFile)) {
                        return new Feedback(
                            isSuccess: false,
                            message: 'Raw file not valid',
                        );
                    }

                    $processedMedia = match ($rawFile->mediaType) {
                        MediaType::Image => $this->processRawFileImage($rawFile, $user),
                        default => $this->processRawFile($rawFile, $user),
                    };

                    if (empty($processedMedia)) {
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
                    $this->logger->logError('Error storing file: '
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
            return $this->imageService->resizeOriginalImage(
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
            path: $rawFile->extraPath,
            filenameOriginal: $rawFile->name,
            filenameLarge: null,
            filenameMedium: null,
            filenameSmall: null,
            filenameXSmall: null,
            captionHtml: null,
            filenameSource: $rawFile->originalName,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    private function generateFilename(string $extension): string
    {
        return date('YmdHis') . StringUtil::generateRandomString(16) . '.' . $extension;
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
        $extension = $this->getFileExtension($rawName);
        $rawNameWithoutExtension = trim(str_replace($extension, '', $rawName), '. ');
        $newName = $mediaType === MediaType::Image
            ? $this->generateFilename($extension)
            : StringUtil::cleanString($rawNameWithoutExtension) . '-' . $this->generateFilename($extension);
        $basePath = rtrim($this->mediaBaseDir, ' /');
        $extraPath = $this->getOrGenerateMediaFolder($basePath);
        $targetPath = $basePath . '/' . $extraPath . '/' . $newName;

        $res = @rename($rawPathWithName, $targetPath);
        if (!$res) {
            $this->logger->logError(
                'Error renaming file from ' . $rawPathWithName . ' to ' . $targetPath
            );
            return null;
        }

        $resP = chmod($targetPath, 0644);
        if (!$resP) {
            $this->logger->logError('Error updating file permissions: ' . $rawPathWithName);
        }

        return new RawFile(
            originalName: $rawName,
            name: $newName,
            basePath: $basePath,
            extraPath: $extraPath,
            extension: $extension,
            mediaType: $mediaType,
            sizeBytes: (int)$rawFiles['files']['size'][0],
            error: $rawFiles['files']['error'][0],
        );
    }

    public function getFileExtension(string $filename): string
    {
        if (!str_contains($filename, '.')) {
            return '';
        }

        $parts = explode('.', $filename);
        return strtolower(trim($parts[count($parts) - 1]));
    }

    public function getOrGenerateMediaFolder(string $mediaBasePath): string
    {
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
            if (false === @mkdir(directory: $fullPath, recursive: true)) {
                $this->logger->logError('Failed to create folder: ' . $fullPath);
                return 'no-folder';
            }

            $count++;
        } while(!is_dir($fullPath));

        return $mediaExtraPath;
    }

    public function getTotalMedia(): array
    {
        return $this->mediaDataLayer->getTotalMedia();
    }
}
