<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Module\Article\Entity\RawFile;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\User\Model\User;
use DateTimeImmutable;
use GdImage;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Article\Model\ImagePath;
use Amora\Core\Util\StringUtil;

class ImageResizeService
{
    const IMAGE_SIZE_MEDIUM = 2;
    const IMAGE_SIZE_LARGE = 3;

    const IMAGE_SIZE_MEDIUM_MAX_HEIGHT = 1200;
    const IMAGE_SIZE_MEDIUM_MAX_WIDTH = 1200;
    const IMAGE_SIZE_LARGE_MAX_HEIGHT = 1600;
    const IMAGE_SIZE_LARGE_MAX_WIDTH = 1600;

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

    public function resizeOriginalImage(
        RawFile $rawFile,
        ?User $user,
    ): Media {
        $filePathOriginal = $rawFile->getPathWithName();
        $fullUrlOriginal = $this->getImageUrlFromPath($filePathOriginal);

        $imageOriginal = new ImagePath($filePathOriginal, $fullUrlOriginal);
        $imageMedium = $this->resizeImageDefault($imageOriginal, self::IMAGE_SIZE_MEDIUM);
        $imageLarge = $this->resizeImageDefault($imageOriginal, self::IMAGE_SIZE_LARGE);

        $now = new DateTimeImmutable();

        return new Media(
            id: null,
            type: MediaType::Image,
            status: MediaStatus::Active,
            user: $user,
            path: $rawFile->path,
            filenameOriginal: $rawFile->name,
            filenameLarge: $imageLarge->fullUrl,
            filenameMedium: $imageMedium->filePath,
            caption: null,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    private function resizeImage(
        ImagePath $image,
        int $newMaxWidth,
        int $newMaxHeight
    ): ?ImagePath {
        list($originalWidth, $originalHeight) = getimagesize($image->filePath);

        if (!$originalWidth || !$originalHeight) {
            $this->logger->logError(
                'Error getting width and/or height of image' .
                ' - Original full path: ' . $image->filePath
            );

            return null;
        }

        if ($newMaxWidth >= $originalWidth && $newMaxHeight >= $originalHeight) {
            $this->logger->logInfo(
                'The new size is smaller than the original size' .
                ' - Returning original full path: ' . $image->filePath
            );

            return $image;
        }

        $ratio = $newMaxWidth / $originalWidth;
        $newWidth = $newMaxWidth;
        $newHeight = (int)round($originalHeight * $ratio);

        if ($newHeight > $newMaxHeight) {
            $ratio = $newMaxHeight / $originalHeight;
            $newWidth = (int)round($originalWidth * $ratio);
            $newHeight = $newMaxHeight;
        }

        $imageTypeExtension = $this->getExtension($image->filePath);
        $newFilename = $this->getNewImageName($imageTypeExtension);
        $outputFullPath = rtrim($this->mediaBaseDir, ' /') . '/'  . $newFilename;
        $outputFullUrl = rtrim($this->mediaBaseUrl, ' /') . '/' . $newFilename;

        $this->detectImageTypeAndResize(
            $imageTypeExtension,
            $image->filePath,
            $outputFullPath,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        if (!file_exists($outputFullPath)) {
            $this->logger->logError(
                'Error resizing image - Keeping original image' .
                ' - Image: ' . $image->filePath
            );
            $originalFilename = $this->getFilenameFromPath($image->filePath);
            $originalFullUrl = rtrim($this->mediaBaseUrl, ' /') . '/' . $originalFilename;
            return new ImagePath($image->filePath, $originalFullUrl);
        }

        return new ImagePath($outputFullPath, $outputFullUrl);
    }

    private function resizeImageDefault(
        ImagePath $image,
        int $newImageSizeConstant,
    ): ?ImagePath {
        $newWidth = $this->getDefaultWidthSize($newImageSizeConstant);
        $newHeight = $this->getDefaultHeightSize($newImageSizeConstant);

        return $this->resizeImage($image, $newWidth, $newHeight);
    }

    private function getNewImageName(string $imageTypeExtension): string
    {
        return date('YmdHis') . StringUtil::getRandomString(16) . '.' . $imageTypeExtension;
    }

    private function getExtension(string $sourceFullPath): ?string
    {
        if (!str_contains($sourceFullPath, '.')) {
            return null;
        }

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
            case self::IMAGE_SIZE_LARGE:
                return self::IMAGE_SIZE_LARGE_MAX_WIDTH;
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
            case self::IMAGE_SIZE_LARGE:
                return self::IMAGE_SIZE_LARGE_MAX_HEIGHT;
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

        $outputImage = $this->checkExifAndRotateIfNecessary($outputImage, $sourceFullPath);

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

        return imagepng($outputImage, $outputFullPath);
    }

    private function checkExifAndRotateIfNecessary(GdImage $image, string $imagePath): GdImage|bool
    {
        $exif = exif_read_data($imagePath, 0, true);

        $orientation = $exif['Orientation'] ?? ($exif['IFD0']['Orientation'] ?? null);

        if(!isset($orientation)) {
            return $image;
        }

        return match ($orientation) {
            8 => imagerotate($image, 90, 0),
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            default => $image,
        };

    }
}
