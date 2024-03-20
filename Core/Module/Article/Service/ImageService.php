<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Module\Article\Entity\ImageExif;
use Amora\Core\Module\Article\Entity\RawFile;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\ImageSize;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\User\Model\User;
use DateTimeImmutable;
use GdImage;
use Amora\Core\Util\Logger;
use Amora\Core\Util\StringUtil;
use Throwable;

readonly class ImageService
{
    public function __construct(
        private Logger $logger,
    ) {}

    public function resizeRawImage(
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

        $exif = $this->getExifData(
            filePathWithName: $rawFile->getPathWithName(),
            extension: $rawFile->extension,
        );

        $now = new DateTimeImmutable();

        return new Media(
            id: null,
            type: $rawFile->mediaType,
            status: MediaStatus::Active,
            user: $user,
            widthOriginal: $exif?->width,
            heightOriginal: $exif?->height,
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

    public function resizeImage(
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

        $smallerSize = $newSize->getSmaller();

        // The new size and the next smaller size are greater than the original width
        if ($newSize->value > $originalWidth &&
            $smallerSize &&
            $smallerSize->value > $originalWidth
        ) {
            return null;
        }

        // The original width stands between the new size and the next smaller size
        if ($newSize->value >= $originalWidth &&
            $smallerSize &&
            $smallerSize->value <= $originalWidth
        ) {
            $ratio = 1;
            $newWidth = $originalWidth;
        } else {
            $ratio = $newSize->value / $originalWidth;
            $newWidth = $newSize->value;
        }

        $newHeight = (int)round($originalHeight * $ratio);

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

    private function getImageFromSourcePath(RawFile $sourceImage): GdImage|null|false
    {
        $imageType = exif_imagetype($sourceImage->getPathWithName());

        if ($imageType === IMAGETYPE_JPEG) {
            $output = @imagecreatefromjpeg($sourceImage->getPathWithName());

            if (!$output) {
                $this->logger->logError('Error creating JPG: ' . $sourceImage->getPathWithName());
            }

            return $output;
        }

        if ($imageType === IMAGETYPE_WEBP) {
            $output = @imagecreatefromwebp($sourceImage->getPathWithName());

            if (!$output) {
                $this->logger->logError('Error creating Webp image: ' . $sourceImage->getPathWithName());
            }

            return $output;
        }

        if ($imageType === IMAGETYPE_PNG) {
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

            $outputImage = $this->checkExifAndRotateIfNecessary($outputImage, $sourceFullPath);

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

    private function checkExifAndRotateIfNecessary(GdImage $image, string $imagePath): GdImage
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

        if (!isset($orientation)) {
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
