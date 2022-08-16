<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Util\Logger;
use Amora\Core\Model\File;
use Amora\Core\Model\Util\QueryOptions;
use Amora\Core\Module\Article\Datalayer\ImageDataLayer;
use Amora\Core\Module\Article\Model\Image;
use Throwable;

class ImageService
{
    public function __construct(
        private Logger $logger,
        private ImageDataLayer $imageDataLayer,
        private ImageResizeService $imageResizeService,
        private string $mediaBaseDir,
    ) {}

    public function getImageForId(int $id): ?Image
    {
        return $this->imageDataLayer->getImageForId($id);
    }

    public function filterImagesBy(
        array $imageIds = [],
        array $userIds = [],
        bool $excludeDeleted = true,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->imageDataLayer->filterImagesBy(
            imageIds: $imageIds,
            userIds: $userIds,
            excludeDeleted: $excludeDeleted,
            queryOptions: $queryOptions,
        );
    }

    public function storeImage(Image $image): Image
    {
        return $this->imageDataLayer->storeImage($image);
    }

    public function deleteImage(Image $image): bool
    {
        $res = $this->imageDataLayer->deleteImage($image->id);
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
            $newImageName = $this->imageResizeService->getNewImageName($file->getExtension());
            $targetPath = rtrim($this->mediaBaseDir, ' /') . '/' . $newImageName;
            $res = rename($file->fullPath, $targetPath);
            if (empty($res)) {
                return $output;
            }

            $output[] = $this->imageResizeService->getImageObjectFromUploadedImageFile(
                imagePath: $targetPath,
                userId: $userId,
            );
        }

        return $output;
    }
}
