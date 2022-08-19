<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Module\Article\Entity\RawFile;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\User\Model\User;
use DateTimeImmutable;
use GdImage;
use Amora\Core\Util\Logger;
use Amora\Core\Util\StringUtil;

class ImageResizeService
{
    const IMAGE_SIZE_MEDIUM = 2;
    const IMAGE_SIZE_LARGE = 3;

    const IMAGE_SIZE_MEDIUM_MAX_HEIGHT = 1200;
    const IMAGE_SIZE_MEDIUM_MAX_WIDTH = 1200;
    const IMAGE_SIZE_LARGE_MAX_HEIGHT = 1600;
    const IMAGE_SIZE_LARGE_MAX_WIDTH = 1600;

    public function __construct(
        private readonly Logger $logger,
    ) {}

    public function resizeOriginalImage(
        RawFile $rawFile,
        ?User $user,
        ?string $extraImagePath = null,
    ): Media {
        $imageMedium = $this->resizeImageDefault($rawFile, self::IMAGE_SIZE_MEDIUM);
        $imageLarge = $this->resizeImageDefault($rawFile, self::IMAGE_SIZE_LARGE);

        $now = new DateTimeImmutable();

        return new Media(
            id: null,
            type: $rawFile->mediaType,
            status: MediaStatus::Active,
            user: $user,
            path: $extraImagePath,
            filenameOriginal: $rawFile->name,
            filenameLarge: $imageLarge->name,
            filenameMedium: $imageMedium->name,
            caption: $rawFile->originalName,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    private function resizeImage(
        RawFile $image,
        int $newMaxWidth,
        int $newMaxHeight,
    ): ?RawFile {
        list($originalWidth, $originalHeight) = getimagesize($image->getPathWithName());

        if (!$originalWidth || !$originalHeight) {
            $this->logger->logError(
                'Error getting width and/or height of image' .
                ' - Original full path: ' . $image->getPathWithName()
            );

            return null;
        }

        if ($newMaxWidth >= $originalWidth && $newMaxHeight >= $originalHeight) {
            $this->logger->logInfo(
                'The new size is smaller than the original size' .
                ' - Returning original full path: ' . $image->getPathWithName()
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

        $newFilename = $this->getNewImageName($image->extension);
        $outputFullPath = rtrim($image->path, ' /') . '/'  . $newFilename;

        $this->detectImageTypeAndResize(
            $image->extension,
            $image->getPathWithName(),
            $outputFullPath,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        if (!file_exists($outputFullPath)) {
            $this->logger->logError(
                'Error resizing image, there is another image with the same name: '
                . $image->getPathWithName()
            );

            return $image;
        }

        return new RawFile(
            originalName: $newFilename,
            name: $newFilename,
            path: $image->path,
            extension: $image->extension,
            mediaType: $image->mediaType,
        );
    }

    private function resizeImageDefault(
        RawFile $image,
        int $newImageSizeConstant,
    ): ?RawFile {
        $newWidth = $this->getDefaultWidthSize($newImageSizeConstant);
        $newHeight = $this->getDefaultHeightSize($newImageSizeConstant);

        return $this->resizeImage($image, $newWidth, $newHeight);
    }

    private function getNewImageName(string $imageTypeExtension): string
    {
        return date('YmdHis') . StringUtil::getRandomString(16) . '.' . $imageTypeExtension;
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

    private function detectImageTypeAndResize(
        string $imageTypeExtension,
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight
    ): void {
        switch ($imageTypeExtension) {
            case 'jpg':
            case 'jpeg':
                $this->resizeJpgImage(
                    $sourceFullPath,
                    $outputFullPath,
                    $newWidth,
                    $newHeight,
                    $originalWidth,
                    $originalHeight
                );
                break;
            case 'webp':
                $this->resizeWebpImage(
                    $sourceFullPath,
                    $outputFullPath,
                    $newWidth,
                    $newHeight,
                    $originalWidth,
                    $originalHeight
                );
                break;
            case 'png':
                $this->resizePngImage(
                    $sourceFullPath,
                    $outputFullPath,
                    $newWidth,
                    $newHeight,
                    $originalWidth,
                    $originalHeight
                );
                break;
            default:
                $this->logger->logWarning(
                    'Image type resizing not supported: ' . $imageTypeExtension .
                    ' - Image path: ' . $sourceFullPath
                );
        }
    }

    private function resizeJpgImage(
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight
    ): void {
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

        imagejpeg($outputImage, $outputFullPath, 85);
    }

    private function resizeWebpImage(
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight
    ): void {
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

        imagewebp($outputImage, $outputFullPath, 85);
    }

    private function resizePngImage(
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight
    ): void {
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

        imagepng($outputImage, $outputFullPath);
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
