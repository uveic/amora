<?php

namespace uve\core\module\article\service;

use uve\core\Logger;
use uve\core\module\article\model\Image;
use uve\core\module\article\model\ImagePath;
use uve\core\util\DateUtil;
use uve\core\util\StringUtil;

class ImageResizeService
{
    const IMAGE_SIZE_MEDIUM = 2;
    const IMAGE_SIZE_BIG = 3;

    const IMAGE_SIZE_MEDIUM_MAX_HEIGHT = 800;
    const IMAGE_SIZE_MEDIUM_MAX_WIDTH = 1200;
    const IMAGE_SIZE_BIG_MAX_HEIGHT = 1800;
    const IMAGE_SIZE_BIG_MAX_WIDTH = 2400;

    private Logger $logger;
    private string $mediaBaseDir;
    private string $mediaBaseUrl;

    public function __construct(
        Logger $logger,
        string $mediaBaseDir,
        string $mediaBaseUrl
    ) {
        $this->logger = $logger;
        $this->mediaBaseDir = rtrim($mediaBaseDir, ' /') . '/';
        $this->mediaBaseUrl = rtrim($mediaBaseUrl, ' /') . '/';
    }

    public function getImageObjectFromUploaded(
        string $imagePath,
        int $userId,
        ?string $caption = null
    ): ?Image {
        $filePathOriginal = $imagePath;
        $fullUrlOriginal = $this->getImageUrlFromPath($filePathOriginal);

        $imageOriginal = new ImagePath($filePathOriginal, $fullUrlOriginal);
        $imageMedium = $this->resizeImageDefault($imageOriginal, self::IMAGE_SIZE_MEDIUM);
        $imageBig = $this->resizeImageDefault($imageOriginal, self::IMAGE_SIZE_BIG);

        $now = DateUtil::getCurrentDateForMySql();

        return new Image(
            null,
            $userId,
            $imageOriginal->getFullUrl(),
            $imageMedium->getFullUrl(),
            $imageBig->getFullUrl(),
            $imageOriginal->getFilePath(),
            $imageMedium->getFilePath(),
            $imageBig->getFilePath(),
            $caption,
            $now,
            $now
        );
    }

    private function resizeImage(
        ImagePath $image,
        int $newMaxWidth,
        int $newMaxHeight
    ): ?ImagePath {
        list($originalWidth, $originalHeight) = getimagesize($image->getFilePath());

        if (!$originalWidth || !$originalHeight) {
            $this->logger->logError(
                'Error getting width and/or height of image' .
                ' - Original full path: ' . $image->getFilePath()
            );

            return null;
        }

        if ($newMaxWidth >= $originalWidth && $newMaxHeight >= $originalHeight) {
            $this->logger->logInfo(
                'Returning original image. New size smaller than original.' .
                ' - Original full path: ' . $image->getFilePath()
            );

            return $image;
        }

        $ratio = $newMaxWidth / $originalWidth;
        $newWidth = $newMaxWidth;
        $newHeight = $originalHeight * $ratio;

        if ($newHeight > $newMaxHeight) {
            $ratio = $newMaxHeight / $originalHeight;
            $newWidth = $originalWidth * $ratio;
            $newHeight = $newMaxHeight;
        }

        $imageTypeExtension = $this->getImageType($image->getFilePath());
        $newFilename = $this->getNewImageName($imageTypeExtension);
        $outputFullPath = rtrim($this->mediaBaseDir, ' /') . '/'  . $newFilename;
        $outputFullUrl = rtrim($this->mediaBaseUrl, ' /') . '/' . $newFilename;

        $this->detectImageTypeAndResize(
            $imageTypeExtension,
            $image->getFilePath(),
            $outputFullPath,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        if (!file_exists($outputFullPath)) {
            $this->logger->logError(
                'Error resizing image - Keeping original image' .
                ' - Image: ' . $image->getFilePath()
            );
            $originalFilename = $this->getFilenameFromPath($image->getFilePath());
            $originalFullUrl = rtrim($this->mediaBaseUrl, ' /') . '/' . $originalFilename;
            return new ImagePath($image->getFilePath(), $originalFullUrl);
        }

        return new ImagePath($outputFullPath, $outputFullUrl);
    }

    private function resizeImageDefault(
        ImagePath $image,
        int $newImageSizeConstant
    ): ?ImagePath {
        $newWidth = $this->getDefaultWidthSize($newImageSizeConstant);
        $newHeight = $this->getDefaultHeightSize($newImageSizeConstant);

        return $this->resizeImage($image, $newWidth, $newHeight);
    }

    public function getNewImageName(string $imageTypeExtension): string
    {
        return date('YmdHis') . StringUtil::getRandomString(16) . '.' . $imageTypeExtension;
    }

    private function getImageType(string $sourceFullPath): ?string
    {
        $parts = explode('.', $sourceFullPath);
        return empty($parts) ? null : strtolower(trim($parts[count($parts) - 1]));
    }

    private function getFilenameFromPath(string $sourceFullPath): ?string
    {
        $parts = explode('/', $sourceFullPath);
        return empty($parts) ? null : strtolower(trim($parts[count($parts) - 1]));
    }

    private function getDefaultWidthSize(int $imageDefaultSize): int
    {
        switch ($imageDefaultSize) {
            case self::IMAGE_SIZE_MEDIUM:
                return self::IMAGE_SIZE_MEDIUM_MAX_WIDTH;
            case self::IMAGE_SIZE_BIG:
                return self::IMAGE_SIZE_BIG_MAX_WIDTH;
            default:
                $this->logger->logWarning('No default image size found, returning medium');
                return self::IMAGE_SIZE_MEDIUM_MAX_WIDTH;
        }
    }

    private function getDefaultHeightSize(int $imageDefaultSize): int
    {
        switch ($imageDefaultSize) {
            case self::IMAGE_SIZE_MEDIUM:
                return self::IMAGE_SIZE_MEDIUM_MAX_HEIGHT;
            case self::IMAGE_SIZE_BIG:
                return self::IMAGE_SIZE_BIG_MAX_HEIGHT;
            default:
                $this->logger->logWarning('No default image size found, returning medium');
                return self::IMAGE_SIZE_MEDIUM_MAX_HEIGHT;
        }
    }

    private function getImageUrlFromPath(string $imagePath): string
    {
        $parts = explode('/', $imagePath);
        return rtrim($this->mediaBaseUrl, ' /') . '/' . $parts[count($parts) - 1];
    }

    private function detectImageTypeAndResize(
        string $imageTypeExtension,
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight
    ): bool {
        switch ($imageTypeExtension) {
            case 'jpg':
            case 'jpeg':
                return $this->resizeJpgImage(
                    $sourceFullPath,
                    $outputFullPath,
                    $newWidth,
                    $newHeight,
                    $originalWidth,
                    $originalHeight
                );
            case 'webp':
                return $this->resizeWebpImage(
                    $sourceFullPath,
                    $outputFullPath,
                    $newWidth,
                    $newHeight,
                    $originalWidth,
                    $originalHeight
                );
            case 'png':
                return $this->resizePngImage(
                    $sourceFullPath,
                    $outputFullPath,
                    $newWidth,
                    $newHeight,
                    $originalWidth,
                    $originalHeight
                );
        }

        $this->logger->logWarning(
            'Image type resizing not supported: ' . $imageTypeExtension .
            ' - Image path: ' . $sourceFullPath
        );

        return false;
    }

    private function resizeJpgImage(
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight
    ): bool {
        $outputImage = imagecreatetruecolor($newWidth, $newHeight);
        $sourceImage = imagecreatefromjpeg($sourceFullPath);
        imagecopyresampled(
            $outputImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        return imagejpeg($outputImage, $outputFullPath, 85);
    }

    private function resizeWebpImage(
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight
    ): bool {
        $outputImage = imagecreatetruecolor($newWidth, $newHeight);
        $sourceImage = imagecreatefromwebp($sourceFullPath);
        imagecopyresampled(
            $outputImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        return imagewebp($outputImage, $outputFullPath, 85);
    }

    private function resizePngImage(
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight
    ): bool {
        $outputImage = imagecreatetruecolor($newWidth, $newHeight);
        $sourceImage = imagecreatefrompng($sourceFullPath);
        imagecopyresampled(
            $outputImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        return imagepng($outputImage, $outputFullPath, 85);
    }
}