<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Logger;
use Amora\Core\Model\File;
use Amora\Core\Module\Article\Datalayer\ImageDataLayer;
use Amora\Core\Module\Article\Model\Image;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;

class ImageService
{
    public function __construct(
        private Logger $logger,
        private ImageDataLayer $imageDataLayer,
        private ImageResizeService $imageResizeService,
        private string $mediaBaseDir,
        private string $mediaBaseUrl,
    ) {}

    public function getImageForId(int $id): ?Image
    {
        return $this->imageDataLayer->getImageForId($id);
    }

    public function getAllImages(): array
    {
        return $this->imageDataLayer->getAllImages();
    }

    public function getImagesForUserId(int $userId): array
    {
        return $this->imageDataLayer->getImagesForUserId($userId);
    }

    public function storeImage(Image $image): ?Image
    {
        return $this->imageDataLayer->storeImage($image);
    }

    public function deleteImage(Image $image): bool
    {
        $res = $this->imageDataLayer->deleteImage($image->getId());
        if (empty($res)) {
            $this->logger->logError('Error deleting image. Image ID: ' . $image->getId());
            return false;
        }

        $imgFullPath = $image->getFilePathOriginal();
        if (file_exists($imgFullPath)) {
            if (!unlink($imgFullPath)) {
                return false;
            }
        }

        if ($image->getFilePathMedium()) {
            $imgFullPath = $image->getFilePathMedium();
            if (file_exists($imgFullPath)) {
                if (!unlink($imgFullPath)) {
                    return false;
                }
            }
        }

        if ($image->getFilePathBig()) {
            $imgFullPath = $image->getFilePathBig();
            if (file_exists($imgFullPath)) {
                if (!unlink($imgFullPath)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function processImages(array $files, int $userId): array
    {
        $output = [];
        $key = 0;
        /** @var File $file */
        foreach ($files as $file) {
            $cleanImgName = DateUtil::getTimestamp() . '_' .
                StringUtil::cleanString($file->getName());

            $targetPath = rtrim($this->mediaBaseDir, ' /') . '/' . $cleanImgName;
            $res = rename($file->getFullPath(), $targetPath);
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
