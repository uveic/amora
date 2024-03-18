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

enum ImageSize: int {
    case XSmall = 250;
    case Small = 350;
    case Medium = 720;
    case Large = 1200;
    case XLarge = 1600;
}

readonly class ImageService
{
    public function __construct(
        private Logger $logger,
    ) {}

    public function resizeOriginalImage(
        RawFile $rawFile,
        ?User $user,
        ?string $captionHtml = null,
        bool $keepSameImageFormat = false,
    ): Media {
        $imageXSmall = $this->resizeImage($rawFile, ImageSize::XSmall, $keepSameImageFormat);
        $imageSmall = $this->resizeImage($rawFile, ImageSize::Small, $keepSameImageFormat);
        $imageMedium = $this->resizeImage($rawFile, ImageSize::Medium, $keepSameImageFormat);
        $imageLarge = $this->resizeImage($rawFile, ImageSize::Large, $keepSameImageFormat);
        $imageXLarge = $this->resizeImage($rawFile, ImageSize::XLarge, $keepSameImageFormat);

        $now = new DateTimeImmutable();

        return new Media(
            id: null,
            type: $rawFile->mediaType,
            status: MediaStatus::Active,
            user: $user,
            path: $rawFile->extraPath,
            filenameOriginal: $rawFile->name,
            filenameXLarge: $imageXLarge?->name,
            filenameLarge: $imageLarge?->name,
            filenameMedium: $imageMedium?->name,
            filenameSmall: $imageSmall?->name,
            filenameXSmall: $imageXSmall?->name,
            captionHtml: $captionHtml,
            filenameSource: $rawFile->originalName,
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
        ImageSize $newImageSize,
        string $imageExtension,
    ): Media {
        $outputImage = $this->resizeImage(
            image: $existingMedia->asRawFile($imageExtension),
            newSize: $newImageSize,
        );

        if (!$outputImage) {
            $this->logger->logError('Error generating small image. Media ID: ' . $existingMedia->id);
            return $existingMedia;
        }

        if ($newImageSize === ImageSize::XSmall && $existingMedia->filenameXSmall) {
            if (file_exists($existingMedia->getDirWithNameXSmall())) {
                unlink($existingMedia->getDirWithNameXSmall());
            }
        } elseif ($newImageSize === ImageSize::Small && $existingMedia->filenameSmall) {
            if (file_exists($existingMedia->getDirWithNameSmall())) {
                unlink($existingMedia->getDirWithNameSmall());
            }
        } elseif ($newImageSize === ImageSize::Medium && $existingMedia->filenameMedium) {
            if (file_exists($existingMedia->getDirWithNameMedium())) {
                unlink($existingMedia->getDirWithNameMedium());
            }
        } elseif ($newImageSize === ImageSize::Large && $existingMedia->filenameLarge) {
            if (file_exists($existingMedia->getDirWithNameLarge())) {
                unlink($existingMedia->getDirWithNameLarge());
            }
        } elseif ($newImageSize === ImageSize::XLarge && $existingMedia->filenameXLarge) {
            if (file_exists($existingMedia->getDirWithNameXLarge())) {
                unlink($existingMedia->getDirWithNameXLarge());
            }
        }

        return new Media(
            id: $existingMedia->id,
            type: $existingMedia->type,
            status: $existingMedia->status,
            user: $existingMedia->user,
            path: $existingMedia->path,
            filenameOriginal: $existingMedia->filenameOriginal,
            filenameXLarge: $newImageSize === ImageSize::XLarge ? $outputImage->name : $existingMedia->filenameXLarge,
            filenameLarge: $newImageSize === ImageSize::Large ? $outputImage->name : $existingMedia->filenameLarge,
            filenameMedium:  $newImageSize === ImageSize::Medium ? $outputImage->name : $existingMedia->filenameMedium,
            filenameSmall:  $newImageSize === ImageSize::Small ? $outputImage->name : $existingMedia->filenameSmall,
            filenameXSmall:  $newImageSize === ImageSize::XSmall ? $outputImage->name : $existingMedia->filenameXSmall,
            captionHtml: $existingMedia->captionHtml,
            filenameSource: $existingMedia->filenameSource,
            createdAt: $existingMedia->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }

    private function resizeImage(
        RawFile $image,
        ImageSize $newSize,
        bool $keepSameImageFormat = false,
    ): ?RawFile {
        list($originalWidth, $originalHeight) = @getimagesize($image->getPathWithName());

        if (!$originalWidth || !$originalHeight) {
            $this->logger->logError(
                'Error getting width and/or height of image' .
                ' - Original full path: ' . $image->getPathWithName()
            );

            return null;
        }

        if ($newSize->value >= $originalWidth && $newSize->value >= $originalHeight) {
            return $image;
        }

        $ratio = $newSize->value / $originalWidth;
        $newWidth = $newSize->value;
        $newHeight = (int)round($originalHeight * $ratio);

        if ($newHeight > $newSize->value) {
            $ratio = $newSize->value / $originalHeight;
            $newWidth = (int)round($originalWidth * $ratio);
            $newHeight = $newSize->value;
        }

        $sourceImage = $this->getImageFromSourcePath($image);
        if (!$sourceImage) {
            $this->logger->logError(
                'Error resizing image. Source image could not be generated for ' . $image->getPathWithName()
            );

            return null;
        }

        if ($keepSameImageFormat) {
            $newFilename = $this->getNewImageName($image->extension);
            $outputFullPath = $image->getPath() . '/'  . $newFilename;

            $this->detectImageTypeAndResize(
                $sourceImage,
                $image->extension,
                $image->getPathWithName(),
                $outputFullPath,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight,
            );
        } else {
            $newFilename = $this->getNewImageName('webp');
            $outputFullPath = $image->getPath() . '/'  . $newFilename;

            $this->resizeWebpImage(
                $sourceImage,
                $image->getPathWithName(),
                $outputFullPath,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight,
            );
        }

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

    private function getNewImageName(string $imageTypeExtension): string
    {
        return date('YmdHis') . StringUtil::generateRandomString(16) . '.' . $imageTypeExtension;
    }

    private function getImageFromSourcePath(RawFile $sourceImage): ?GdImage
    {
        if ($sourceImage->extension === 'jpg' || $sourceImage->extension === 'jpeg') {
            $output = @imagecreatefromjpeg($sourceImage->getPathWithName());

            if (!$output) {
                $this->logger->logError('Error creating JPG: ' . $sourceImage->getPathWithName());
            }

            return $output;
        }

        if ($sourceImage->extension === 'webp') {
            $output = @imagecreatefromwebp($sourceImage->getPathWithName());

            if (!$output) {
                $this->logger->logError('Error creating Webp image: ' . $sourceImage->getPathWithName());
            }

            return $output;
        }

        if ($sourceImage->extension === 'png') {
            $output = @imagecreatefrompng($sourceImage->getPathWithName());

            if (!$output) {
                $this->logger->logError('Error creating PNG image: ' . $sourceImage->getPathWithName());
            }

            return $output;
        }

        $this->logger->logWarning(
            'Image type not supported (resizing): ' . $sourceImage->extension . ' - Image path: ' . $sourceImage->getPathWithName()
        );

        return null;
    }

    private function detectImageTypeAndResize(
        GdImage $sourceImage,
        string $imageTypeExtension,
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight,
    ): void {
        if ($imageTypeExtension === 'jpg' || $imageTypeExtension === 'jpeg') {
            $this->resizeJpgImage(
                $sourceImage,
                $sourceFullPath,
                $outputFullPath,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight,
            );

            return;
        }

        if ($imageTypeExtension === 'webp') {
            $this->resizeWebpImage(
                $sourceImage,
                $sourceFullPath,
                $outputFullPath,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight,
            );

            return;
        }

        if ($imageTypeExtension === 'png') {
            $this->resizePngImage(
                $sourceImage,
                $sourceFullPath,
                $outputFullPath,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight,
            );

            return;
        }

        $this->logger->logWarning(
            'Image type not supported (resizing): ' . $imageTypeExtension . ' - Image path: ' . $sourceFullPath
        );
    }

    private function resizeJpgImage(
        GdImage $sourceImage,
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight,
    ): void {
        try {
            $outputImage = imagecreatetruecolor($newWidth, $newHeight);

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
                $originalHeight,
            );

            $outputImage = $this->checkExifAndRotateIfNecessary($outputImage, $sourceFullPath);

            $res = imagejpeg($outputImage, $outputFullPath, 85);

            if (!$res) {
                $this->logger->logError('Error resizing JPG image: ' . $outputFullPath);
            }

            imagedestroy($outputImage);
            imagedestroy($sourceImage);
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error resizing JPG image: ' . $sourceFullPath
                . ' - Error: ' . $t->getMessage(),
            );
        }
    }

    private function resizeWebpImage(
        GdImage $sourceImage,
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight,
    ): void {
        try {
            $outputImage = imagecreatetruecolor($newWidth, $newHeight);

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

            $res = imagewebp($outputImage, $outputFullPath, 85);

            if (!$res) {
                $this->logger->logError('Error resizing Webp image: ' . $outputFullPath);
            }

            imagedestroy($outputImage);
            imagedestroy($sourceImage);
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error resizing Webp image: ' . $sourceFullPath
                . ' - Error: ' . $t->getMessage(),
            );
        }
    }

    private function resizePngImage(
        GdImage $sourceImage,
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight,
    ): void {
        try {
            $outputImage = imagecreatetruecolor($newWidth, $newHeight);

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
                $originalHeight,
            );

            $res = imagepng($outputImage, $outputFullPath);

            if (!$res) {
                $this->logger->logError('Error resizing PNG image: ' . $outputFullPath);
            }

            imagedestroy($outputImage);
            imagedestroy($sourceImage);
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error resizing PNG image: ' . $sourceFullPath
                . ' - Error: ' . $t->getMessage(),
            );
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
