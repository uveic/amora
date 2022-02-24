<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Util\Logger;
use Amora\Core\Model\File;
use Amora\Core\Model\Util\QueryOptions;
use Amora\Core\Module\Article\Datalayer\ImageDataLayer;
use Amora\Core\Module\Article\Model\Image;

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

    public function storeImage(Image $image): ?Image
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

//        $imgFullPath = $image->getFilePathOriginal();
//        if (file_exists($imgFullPath)) {
//            if (!unlink($imgFullPath)) {
//                return false;
//            }
//        }
//
//        if ($image->getFilePathMedium()) {
//            $imgFullPath = $image->getFilePathMedium();
//            if (file_exists($imgFullPath)) {
//                if (!unlink($imgFullPath)) {
//                    return false;
//                }
//            }
//        }
//
//        if ($image->getFilePathBig()) {
//            $imgFullPath = $image->getFilePathBig();
//            if (file_exists($imgFullPath)) {
//                if (!unlink($imgFullPath)) {
//                    return false;
//                }
//            }
//        }

        return true;
    }

    public function processImages(array $files, int $userId): array
    {
        $output = [];
        $key = 0;
        /** @var File $file */
        foreach ($files as $file) {
            $targetPath = rtrim($this->mediaBaseDir, ' /') . '/' . $file->name;
            $res = rename($file->fullPath, $targetPath);
            if (empty($res)) {
                return $output;
            }

            $current = $this->imageResizeService->getImageObjectFromUploadedImageFile(
                $targetPath,
                $userId
            );
            $output[] = $current;

            $key++;
        }

        return $output;
    }
}
