<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Database\Model\TransactionResponse;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\Logger;
use Amora\Core\Model\File;
use Amora\Core\Model\Util\QueryOptions;
use Amora\Core\Module\Article\Datalayer\MediaDataLayer;
use Amora\Core\Module\Article\Model\Image;
use DateTimeImmutable;
use Throwable;

class ImageService
{
    public function __construct(
        private Logger $logger,
        private MediaDataLayer $mediaDataLayer,
        private ImageResizeService $imageResizeService,
        private string $mediaBaseDir,
    ) {}

    public function getImageForId(int $id): ?Image
    {
        return $this->mediaDataLayer->getImageForId($id);
    }

    public function filterImagesBy(
        array $imageIds = [],
        array $userIds = [],
        bool $excludeDeleted = true,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->mediaDataLayer->filterImagesBy(
            imageIds: $imageIds,
            userIds: $userIds,
            excludeDeleted: $excludeDeleted,
            queryOptions: $queryOptions,
        );
    }

    public function storeImage(Image $image): Image
    {
        return $this->mediaDataLayer->storeImage($image);
    }

    public function deleteImage(Image $image): bool
    {
        $res = $this->mediaDataLayer->deleteImage($image->id);
        if (empty($res)) {
            $this->logger->logError('Error deleting image. Image ID: ' . $image->id);
            return false;
        }

        if (file_exists($image->filePathOriginal)) {
            if (!unlink($image->filePathOriginal)) {
                return false;
            }
        }

        if ($image->filePathMedium) {
            if (file_exists($image->filePathMedium)) {
                if (!unlink($image->filePathMedium)) {
                    return false;
                }
            }
        }

        if ($image->filePathLarge) {
            if (file_exists($image->filePathLarge)) {
                if (!unlink($image->filePathLarge)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function processAndStoreRawImages(array $rawImages, ?int $userId): array
    {
        try {
            $images = $this->convertImagesDataToObjects(
                files: $rawImages,
                userId: $userId,
            );

            /** @var Image $image */
            foreach ($images as $image) {
                $this->storeImage($image);
            }

            return $images;
        } catch (Throwable $t) {
            $this->logger->logError(
                'AuthorisedApiController - Error processing image: ' .
                $t->getMessage() .
                ' - Trace: ' . $t->getTraceAsString()
            );

            return [];
        }
    }

    private function convertImagesDataToObjects(array $files, ?int $userId = null): array
    {
        $output = [];
        /** @var File $file */
        foreach ($files as $file) {
            try {
                $newImageName = $this->imageResizeService->getNewImageName($file->getExtension());
                $targetPath = rtrim($this->mediaBaseDir, ' /') . '/' . $newImageName;

                if (empty($file->fullPath)) {
                    $this->logger->logError('Image not valid, empty path.');
                    continue;
                }

                $res = rename($file->fullPath, $targetPath);
                if (!$res) {
                    $this->logger->logError('Image not valid: ' . $file->fullPath);
                    continue;
                }

                $resP = chmod($targetPath, 0644);
                if (!$resP) {
                    $this->logger->logError('Error updating image permissions: ' . $targetPath);
                }

                $output[] = $this->imageResizeService->getImageObjectFromUploadedImageFile(
                    imagePath: $targetPath,
                    userId: $userId,
                );
            } catch (Throwable $t) {
                $this->logger->logError('Error processing image: ' . $t->getMessage());
                continue;
            }
        }

        return $output;
    }

    public function workflowStoreFile(
        File $rawFile,
        MediaType $mediaType,
        MediaStatus $mediaStatus,
        ?User $user,
    ): ?Media {
        $res = $this->mediaDataLayer->getDb()->withTransaction(
            function () use (
                $mediaType,
                $mediaStatus,
                $rawFile,
                $user,
            ) {
                try {
                    $processedFile = match ($mediaType) {
                        MediaType::Image => $this->processImagesAndGetFile($rawFile, $user->id),
                        MediaType::PDF => $this->processFile($rawFile),
                    };

                    if (empty($processedFile)) {
                        return new TransactionResponse(
                            isSuccess: false,
                            message: 'File not valid.',
                        );
                    }

                    $now = new DateTimeImmutable();
                    $output = $this->mediaDataLayer->storeFile(
                        new Media(
                            id: null,
                            type: $mediaType,
                            status: $mediaStatus,
                            user: $user,
                            path: $processedFile->fullPath,
                            filenameOriginal: $rawFile->name,
                            filenameLarge: null,
                            filenameMedium: null,
                            caption: null,
                            createdAt: $now,
                            updatedAt: $now,
                        )
                    );

                    return new TransactionResponse(
                        isSuccess: true,
                        response: $output,
                    );
                } catch (Throwable $t) {
                    $this->logger->logError('Error storing file: ' . $t->getMessage());
                    return new TransactionResponse(false);
                }
            }
        );

        return $res->getResponse();
    }
}
