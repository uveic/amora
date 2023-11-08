<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Module\Article\Entity\ImageExif;
use Amora\Core\Module\Article\Entity\RawFile;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\User\Model\User;
use DateTimeImmutable;
use GdImage;
use Amora\Core\Util\Logger;
use Amora\Core\Util\StringUtil;
use Throwable;

class ImageService
{
    const IMAGE_SIZE_SMALL = 1;
    const IMAGE_SIZE_MEDIUM = 2;
    const IMAGE_SIZE_LARGE = 3;

    const IMAGE_SIZE_SMALL_MAX_HEIGHT = 600;
    const IMAGE_SIZE_SMALL_MAX_WIDTH = 600;
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
        ?bool $includeLarge = false,
    ): Media {
        $imageSmall = $this->resizeImageDefault($rawFile, self::IMAGE_SIZE_SMALL);
        $imageMedium = $this->resizeImageDefault($rawFile, self::IMAGE_SIZE_MEDIUM);
        $imageLarge = $includeLarge
            ? $this->resizeImageDefault($rawFile, self::IMAGE_SIZE_LARGE)
            : null;

        $now = new DateTimeImmutable();

        return new Media(
            id: null,
            type: $rawFile->mediaType,
            status: MediaStatus::Active,
            user: $user,
            path: $rawFile->extraPath,
            filenameOriginal: $rawFile->name,
            filenameLarge: $imageLarge?->name,
            filenameMedium: $imageMedium->name,
            filenameSmall: $imageSmall->name,
            captionHtml: $rawFile->originalName,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function getExifData(string $filePathWithName, string $extension): ?ImageExif
    {
        if ($extension !== 'jpg' && $extension !== 'jpeg') {
            return null;
        }

        try {
            $exif = @exif_read_data($filePathWithName, 'FILE, IFD0, EXIF, ANY_TAG');
            $date = $exif['DateTimeOriginal'] ?? $exif['DateTime'] ?? null;
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error reading EXIF data. File: ' . $filePathWithName
                . ' - Error: ' . $t->getMessage()
            );
            return null;
        }

        return new ImageExif(
            width: isset($exif['COMPUTED']['Width']) ? (int)$exif['COMPUTED']['Width'] : null,
            height: isset($exif['COMPUTED']['Height']) ? (int)$exif['COMPUTED']['Height'] : null,
            sizeBytes: isset($exif['FileSize']) ? (int)$exif['FileSize'] : null,
            cameraModel: $exif['Model'] ?? null,
            date: $date ? DateTimeImmutable::createFromFormat('Y:m:d H:i:s', $date) : null,
            exposureTime: $exif['ExposureTime'] ?? null,
            ISO: isset($exif['ISOSpeedRatings'])
                ? (is_array($exif['ISOSpeedRatings']) ? $exif['ISOSpeedRatings'][0] : $exif['ISOSpeedRatings'])
                : null,
            rawDataJson: json_encode($exif),
        );
    }

    public function generateResizedImage(
        Media $existingMedia,
        int $newImageSizeConstant,
        string $imageExtension,
    ): Media {
        if ($newImageSizeConstant !== self::IMAGE_SIZE_SMALL
            && $newImageSizeConstant !== self::IMAGE_SIZE_MEDIUM
            && $newImageSizeConstant !== self::IMAGE_SIZE_LARGE
        ) {
            $this->logger->logError('Image size constant not valid. Aborting...');
            return $existingMedia;
        }

        $imageSmall = $this->resizeImageDefault(
            image: $existingMedia->asRawFile($imageExtension),
            newImageSizeConstant: $newImageSizeConstant,
        );

        if (!$imageSmall) {
            $this->logger->logError('Error generating small image. Media ID: ' . $existingMedia->id);
            return $existingMedia;
        }

        if ($newImageSizeConstant === self::IMAGE_SIZE_SMALL && $existingMedia->filenameSmall) {
            if (file_exists($existingMedia->getDirWithNameSmall())) {
                unlink($existingMedia->getDirWithNameSmall());
            }
        } elseif ($newImageSizeConstant === self::IMAGE_SIZE_MEDIUM && $existingMedia->filenameMedium) {
            if (file_exists($existingMedia->getDirWithNameMedium())) {
                unlink($existingMedia->getDirWithNameMedium());
            }
        } elseif ($newImageSizeConstant === self::IMAGE_SIZE_LARGE && $existingMedia->filenameLarge) {
            if (file_exists($existingMedia->getDirWithNameLarge())) {
                unlink($existingMedia->getDirWithNameLarge());
            }
        }

        return new Media(
            id: $existingMedia->id,
            type: $existingMedia->type,
            status: $existingMedia->status,
            user: $existingMedia->user,
            path: $existingMedia->path,
            filenameOriginal: $existingMedia->filenameOriginal,
            filenameLarge: $existingMedia->filenameLarge,
            filenameMedium: $existingMedia->filenameMedium,
            filenameSmall: $imageSmall->name,
            captionHtml: $existingMedia->captionHtml,
            createdAt: $existingMedia->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }

    private function resizeImage(
        RawFile $image,
        int $newMaxWidth,
        int $newMaxHeight,
    ): ?RawFile {
        list($originalWidth, $originalHeight) = @getimagesize($image->getPathWithName());

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
        $outputFullPath = $image->getPath() . '/'  . $newFilename;

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
            basePath: $image->basePath,
            extraPath: $image->extraPath,
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
        return date('YmdHis') . StringUtil::generateRandomString(16) . '.' . $imageTypeExtension;
    }

    private function getDefaultWidthSize(int $imageDefaultSize): int
    {
        switch ($imageDefaultSize) {
            case self::IMAGE_SIZE_SMALL:
                return self::IMAGE_SIZE_SMALL_MAX_WIDTH;
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
            case self::IMAGE_SIZE_SMALL:
                return self::IMAGE_SIZE_SMALL_MAX_HEIGHT;
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

        $res = imagejpeg($outputImage, $outputFullPath, 90);

        if (!$res) {
            $this->logger->logError('Error resizing image: ' . $outputFullPath);
        }
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

        $res = imagewebp($outputImage, $outputFullPath, 90);

        if (!$res) {
            $this->logger->logError('Error resizing image: ' . $outputFullPath);
        }
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

        $res = imagepng($outputImage, $outputFullPath);

        if (!$res) {
            $this->logger->logError('Error resizing image: ' . $outputFullPath);
        }
    }

    private function checkExifAndRotateIfNecessary(GdImage $image, string $imagePath): GdImage|bool
    {
        try {
            $exif = @exif_read_data($imagePath, 0, true);
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error reading EXIF data. Image path: ' . $imagePath
                . ' - Error: ' . $t->getMessage()
            );
            return $image;
        }

        if (!$exif) {
            return $image;
        }

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
